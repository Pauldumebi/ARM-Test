<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    private function RandomCode($length = 15)
    {
        $code = '';
        $total = 0;
        do {
            if (rand(0, 1) == 0) {
                $code .= chr(rand(97, 122)); // ASCII code from **a(97)** to **z(122)**
            } else {
                $code .= rand(0, 9); // Numbers!!
            }
            $total++;
        } while ($total < $length);
        return $code;
    }

    public function createCompanyUser(Request $req)
    {
        $token = $req->token;
        $firstname = $req->firstName;
        $lastname = $req->lastName;
        $email = $req->email;
        $email_suffix = explode("@", $req->email)[1];
        $tel = $req->tel;
        $hash = password_hash("LearningPlatform", PASSWORD_DEFAULT);
        $newtoken = $this->RandomCode();

        try {
            if (DB::table("users")->where("userEmail", "=", $email)->doesntExist()) {

                $query = DB::table("users")->join("company", "users.companyID", "=", "company.companyID")->select(["company.emailSuffix", "company.companyID"])->where("users.token", "=", $token)->where("users.userRoleID", "=", 1)->get();

                if (count($query) > 0) {
                    if ($query[0]->emailSuffix === $email_suffix) {
                        $companyID = $query[0]->companyID;

                        DB::table("users")->insert(["userFirstName" => $firstname, "userLastName" => $lastname, "userEmail" => $email, "userPhone" => $tel, "userPassword" => $hash, "userRoleID" => 2, "companyID" => $companyID, "token" => $newtoken]);

                        return response()->json(["success" => true, "message" => "User Account Created"]);
                    } else {
                        return response()->json(["success" => false, "message" => "User Email not Company Email"], 400);
                    }
                } else {
                    return response()->json(["success" => false, "message" => "Not Admin User"], 401);
                }
            } else {
                return response()->json(["success" => false, "message" => "User Already Registered"], 400);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function getCompanyUsers(Request $req)
    {

        $token = $req->token;
        try {
            $query = DB::table("users")->where("token", "=", $token)->where("userRoleID", "=", 1)->select(["companyID"])->get();

            if (count($query) > 0) {
                $companyID = $query[0]->companyID;

                $users = DB::table("users")->where("companyID", "=", $companyID)->where("userRoleID", "=", 2)->select("userFirstName", "userLastname", "userEmail", "token AS usertoken")->get();

                if (count($users) > 0) {
                    return response()->json(["success" => true, "data" => $users]);
                } else {
                    return response()->json(["success" => true, "message" => "No Users Available"]);
                }
            } else {
                return response()->json(["success" => false, "message" => "Not Admin User"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }
}
