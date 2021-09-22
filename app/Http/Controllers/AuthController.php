<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    private function RandomCode($length = 15)
    {
        $code = '';
        $total = 0;
        do {
            if (rand(0, 1) == 0) {
                $code .= chr(rand(97, 122)); // ASCII code from **a(97)** to **z(122)**
            } else {
                $code .= rand(0, 32); // Numbers!!
            }
            $total++;
        } while ($total < $length);
        return $code;
    }

    // each endpoint will have a function
    public function signup(Request $req)
    {
        $companyName = $req->companyName;
        $companyAddress = $req->companyAddress;
        $companyEmailSuffix = $req->companyEmailSuffix;
        $firstname = $req->firstName;
        $lastname = $req->lastName;
        $email = $req->email;
        $adminRole = $req->adminRole;
        $tel = $req->tel;
        $hash = password_hash($req->password, PASSWORD_DEFAULT);
        $token = $this->RandomCode();
        $email_token = $this->RandomCode();

        try {
            if (DB::table("users")->join("company", "users.companyID", "=", "company.companyID")->where("users.userEmail", "=", $email)->orWhere("company.companyName", "=", $companyName, "or")->orWhere("company.emailSuffix", "=", $companyEmailSuffix)->doesntExist()) {
                $id = DB::table("users")->insertGetId(
                    ["userFirstName" => $firstname, "userLastName" => $lastname, "userEmail" => $email, "userPhone" => $tel, "userPassword" => $hash, "userRoleID" => 1, "token" => $token, "email_token"=> $email_token, "verified_status" => "unverified"],
                );

                $companyID = DB::table("company")->insertGetId([
                    "companyName" => $companyName,
                    "companyAddress1" => $companyAddress,
                    "companyAdminID" => $id,
                    "emailSuffix" => $companyEmailSuffix,
                    "companyAdminRole" => $adminRole
                ]);

                $updatedID = DB::table("users")->where("userEmail", "=", $email)->update([
                    "companyID" => $companyID
                ]);

                $query = DB::table("users")->where("userEmail", "=", $email)->select(["token"])->get();
                $userData = ["token" => $query[0]->token, "role" => "admin"];
                $this-> sendEmail($firstname, $email, $email_token);
                return response()->json(["success" => true, "data" => $userData, "message"=> 'Email sent, please check your inbox']);
            } else {
                return response()->json(["success" => false, "message" => "Company or Admin User Already Exist"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    private function sendEmail ($firstname, $email, $email_token) {
        $details = [
            'name' => $firstname,
            'email' => $email,
            'link' => 'https://learningplatform.sandbox.9ijakids.com/verifyemail?'.$email_token,
            'websiteLink' => 'https://learningplatform.sandbox.9ijakids.com'
        ];
   
        \Mail::to($email)->send(new \App\Mail\VerifyEmail($details));
    }

    public function login(Request $req)
    {
        $email = $req->email;
        $password = $req->password;

        try {
            $query = DB::table("users")->join("role", "users.userRoleID", "=", "role.roleID")->where("users.userEmail", "=", $email)->select(["users.*", "role.roleName"])->get();

            if (count($query) === 1) {
                // $realPassword = $users["password"];
                $user = $query[0];
                $pass_ok = password_verify($password, $user->userPassword);
                if ($pass_ok) {
                    $userData = ["token" => $user->token, "role" => $user->roleName,];
                    return response()->json(["success" => true, "data" => $userData], 200);
                } else {
                    return response()->json(["success" => false, "message" => "Invalid email or password"], 401);
                }
            } else {
                return response()->json(["success" => false, "message" => "User not registered"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }
}
