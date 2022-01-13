<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    private function sendUserCreationEmail($firstname, $email, $password)
    {
        $details = [
            'name' => $firstname,
            'password' => $password,
            'login' => 'https://learningplatform.sandbox.9ijakids.com/login',

        ];

        Mail::to($email)->send(new \App\Mail\CreateUser($details));
    }

    public function createCompanyUser(Request $req)
    {
        $token = $req->token;
        $firstname = $req->firstName;
        $lastname = $req->lastName;
        $email = $req->email;
        $email_suffix = explode("@", $req->email)[1];
        $tel = $this->formatIntlPhoneNo($req->tel);
        $gender = $req->gender;
        $grade = $req->grade;
        $roleName = $req->roleName;
        $hash = password_hash("LearningPlatform", PASSWORD_DEFAULT);
        $newtoken = $this->RandomCode();
        $courseCategory= $req->courseCategory;
        


        if (DB::table("users")->where("userEmail", "=", $email)->doesntExist()) {

            $query = DB::table("users")->join("company", "users.companyID", "=", "company.companyID")->select(["company.emailSuffix", "company.companyID"])->where("users.token", "=", $token)->where("users.userRoleID", "=", 1)->get();


            if ($query[0]->emailSuffix === $email_suffix) {
                $companyID = $query[0]->companyID;

                $queryForGroupCategory = DB::table("groupRole")->where("roleName", "=", $roleName)->get();
                //Get user groupRoleID for either Agent, Supervisor, or Manager
                $groupRoleId = $queryForGroupCategory[0]->groupRoleId;

                DB::table("users")->insert(["userFirstName" => $firstname, "userLastName" => $lastname, "userEmail" => $email, "userPhone" => $tel, "userGender" => $gender, "userGrade"=> $grade, "userPassword" => $hash, "userRoleID" => 2, "groupRoleId" => $groupRoleId, "companyID" => $companyID, "token" => $newtoken]);

                $courses= DB::table('course')->where("roleName","=", $queryForGroupCategory)->get();
                foreach ($courses as $course){
                   $courseID=$course->courseID;
                  $query= DB::table('course')->select("courseName","=", $courseName, "courseDescription","=",$courseDescription)->limit(4)->get();
                   return response()->json(["success"=> true, "message"=>[$query,"Here are some recommended courses you can take."]]);

                }

                $this->sendUserCreationEmail($firstname, $email, "LearningPlatform");

                return response()->json(["success" => true, "message" => "User Account Created"]);
            } else {
                return response()->json(["success" => false, "message" => "User Email not Company Email"], 400);
            }
        } else {
            return response()->json(["success" => false, "message" => "User Already Registered"], 400);
        }
    }

    public function editCompanyUser (Request $req) {
        $adminToken = $req->adminToken;
        $firstname = $req->firstName;
        $lastname = $req->lastName;
        $email = $req->email;
        $userToken = $req->userToken;
        $email_suffix = explode("@", $req->email)[1];
        $tel = $this->formatIntlPhoneNo($req->tel);
        $gender = $req->gender;
        $grade = $req->grade;

        if ($userToken) {
            $queryUserTable = DB::table("users")->where("token", "=", $userToken)->orWhere("userEmail", "=", $email)->get();
        } else
            $queryUserTable = DB::table("users")->where("userEmail", "=", $email)->get();

        
        if (count($queryUserTable) === 1) {
            
            $query = DB::table("users")->join("company", "users.companyID", "=", "company.companyID")->select(["company.emailSuffix", "company.companyID"])->where("users.token", "=", $adminToken)->where("users.userRoleID", "=", 1)->get();
            $userID = $queryUserTable[0]->userID;

            $adminCompanyID = $query[0]->companyID;
            $userCompanyID = $queryUserTable[0]->companyID;

            if ($adminCompanyID === $userCompanyID) {
            
                if ($query[0]->emailSuffix === $email_suffix) {

                    DB::table("users")->where("userID", "=", $userID)->update(["userFirstName" => $firstname, "userLastName" => $lastname, "userEmail" => $email, "userPhone" => $tel, "userGender" => $gender, "userGrade"=> $grade]);

                    return response()->json(["success" => true, "message" => "User successfully updated"]);
                } else {
                    return response()->json(["success" => false, "message" => "User Email not Company Email"], 400);
                }
            }else 
            return response()->json(["success" => true, "message" => "Admin does not belong to this user's company"]);
        } else {
            return response()->json(["success" => false, "message" => "User does not exist"], 400);
        }
    }
    
    public function deleteCompanyUser (Request $req) {
        $adminToken = $req->adminToken;
        $userID = $req->userID;
        $userToken = $req->userToken;

        $table = DB::table("users")->where("token", "=", $adminToken)->get();
        $adminCompanyID = $table[0]->companyID;

        $query = DB::table("users")->where("userID", "=", $userID)->orWhere("token", "=", $userToken)->get();
        
        if (count($query) === 1) {
            $userCompanyID = $query[0]->companyID;

            if ($adminCompanyID === $userCompanyID) {
                DB::table("users")->where("userID", "=", $userID)->delete();
                return response()->json(["success" => true, "message" => "User successfully deleted"]);
            }else 
                return response()->json(["success" => true, "message" => "Admin does not belong to this user's company"]);
            
        } else {
            return response()->json(["success" => false, "message" => "User does not exist"], 400);
        }
    }

    public function getCompanyUsers(Request $req)
    {

        $token = $req->token;

        $query = DB::table("users")->where("token", "=", $token)->where("userRoleID", "=", 1)->select(["companyID"])->get();


        $companyID = $query[0]->companyID;

        $users = DB::table("users")->where("companyID", "=", $companyID)->where("userRoleID", "=", 2)->select("userFirstName", "userLastname", "userEmail", "token AS usertoken")->get();

        if (count($users) > 0) {
            return response()->json(["success" => true, "users" => $users]);
        } else {
            return response()->json(["success" => true, "users" => [], "message" => "No Users Available"]);
        }
    }
}
