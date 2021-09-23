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

<<<<<<< HEAD
        try {
            if (DB::table("users")->join("company", "users.companyID", "=", "company.companyID")->where("users.userEmail", "=", $email)->orWhere("company.companyName", "=", $companyName, "or")->orWhere("company.emailSuffix", "=", $companyEmailSuffix)->doesntExist()) {
                $id = DB::table("users")->insertGetId(
                    ["userFirstName" => $firstname, "userLastName" => $lastname, "userEmail" => $email, "userPhone" => $tel, "userPassword" => $hash, "userRoleID" => 1, "token" => $token, "email_token" => $email_token, "verified_status" => "unverified"],
                );

                $companyID = DB::table("company")->insertGetId([
                    "companyName" => $companyName,
                    "companyAddress1" => $companyAddress,
                    "companyAdminID" => $id,
                    "emailSuffix" => $companyEmailSuffix,
                    "companyAdminRole" => $adminRole
                ]);

                // $companyID = DB::table("company")->insertGetId([
                //     "companyName" => $comName,
                //     "companyAddress1" => $comAdr,
                //     "companyAdminID" => $id,
                //     "emailSuffix" => $comEmailSuffix,
                //     "companyAdminRole" => $adminRole
                // ]);

                $query = DB::table("users")->where("userEmail", "=", $email)->select(["token"])->get();
                $userData = ["token" => $query[0]->token, "role" => "admin"];
                $this->sendEmail($firstname, $email, $email_token);
                return response()->json(["success" => true, "data" => $userData, "message" => 'Email sent, please check your inbox']);
            } else {
                return response()->json(["success" => false, "message" => "Company or Admin User Already Exist"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
=======
        if (DB::table("users")->join("company", "users.companyID", "=", "company.companyID")->where("users.userEmail", "=", $email)->orWhere("company.companyName", "=", $companyName, "or")->orWhere("company.emailSuffix", "=", $companyEmailSuffix)->doesntExist()) {
            $id = DB::table("users")->insertGetId(
                ["userFirstName" => $firstname, "userLastName" => $lastname, "userEmail" => $email, "userPhone" => $tel, "userPassword" => $hash, "userRoleID" => 1, "token" => $token, "email_token"=> $email_token, "verified_status" => "unverified"],
            );

            DB::table("company")->insertGetId([
                "companyName" => $companyName,
                "companyAddress1" => $companyAddress,
                "companyAdminID" => $id,
                "emailSuffix" => $companyEmailSuffix,
                "companyAdminRole" => $adminRole
            ]);

            $query = DB::table("users")->where("userEmail", "=", $email)->select(["token"])->get();
            $userData = ["token" => $query[0]->token, "role" => "admin"];
            $this-> sendVerifyEmail($firstname, $email, $email_token);
            return response()->json(["success" => true, "data" => $userData, "message"=> 'Email sent, please check your inbox']);
        } else {
            return response()->json(["success" => false, "message" => "Company or Admin User Already Exist"], 401);
>>>>>>> 3d7bc193d64ca2f2aca0cef42140c991fd52547f
        }
    }

<<<<<<< HEAD
    private function sendEmail($firstname, $email, $email_token)
    {
=======
    private function sendVerifyEmail ($firstname, $email, $email_token) {
>>>>>>> 3d7bc193d64ca2f2aca0cef42140c991fd52547f
        $details = [
            'name' => $firstname,
            'email' => $email,
            'link' => 'https://learningplatform.sandbox.9ijakids.com/verifyemail?' . $email_token,
            'websiteLink' => 'https://learningplatform.sandbox.9ijakids.com'
        ];

        \Mail::to($email)->send(new \App\Mail\VerifyEmail($details));
    }

    public function verifyEmail($email_token)
    {
        $tokenExists = DB::table('users')->where('email_token', '=', $email_token);
        if ($tokenExists) {
            DB::table("users")->where("email_token", "=", $email_token)->update(["email_token" => ""]);
            return response()->json(["success" => true, "message" => "Email verified, please login"]);
        } else {
            return response()->json(["success" => false, "message" => "Link has expired please login"], 400);
        }
    }

    public function login(Request $req)
    {
        $email = $req->email;
        $password = $req->password;


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
    }

    public function forgotPassword(Request $req)
    {
        $email = $req->email;
        $forgot_password_token = $this->RandomCode();
        $userExists = DB::table('users')->where('userEmail', '=', $email);
        if ($userExists) {
            DB::table('users')->where('userEmail', '=', $email)->update(["forgot_password_token", $forgot_password_token]);
            $this-> sendForgotPasswordEmail($email, $forgot_password_token);
            return response()->json(["success" => true, "message" => "An email has been sent to you."]);
        } else {
            return response()->json(["success" => false, "message" => "Users does not exist"], 400);
        }
    }

    private function sendForgotPasswordEmail ($email, $forgot_password_token) {
        $details = [
            'email' => $email,
            'resetPasswordLink' => 'https://learningplatform.sandbox.9ijakids.com/forgot-password?'.$forgot_password_token,
            'websiteLink' => 'https://learningplatform.sandbox.9ijakids.com'
        ];
   
        \Mail::to($email)->send(new \App\Mail\ForgotPassword($details));
    }

    public function updateForgotPassword(Request $req)
    {
        $token = $req->token;
        $newPassword = $req->newPassword;
        $tokenExists = DB::table('users')->where('forgot_password_token', '=', $token);
        if ($tokenExists) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            DB::table("users")->where("forgot_password_token", "=", $token)->update(["userPassword" => $hash, "forgot_password_token" => ""]);
            return response()->json(["success" => true, "message" => "password reset successful"]);
        } else {
            return response()->json(["success" => false, "message" => "Link has expired please login"], 400);
        }
    }
}