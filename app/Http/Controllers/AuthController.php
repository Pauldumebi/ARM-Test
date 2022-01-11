<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    private function sendVerifyEmail($firstname, $email, $email_token)
    {
        $details = [
            'name' => $firstname,
            'email' => $email,
            'link' => 'https://learningplatform.sandbox.9ijakids.com/verifyemail/' . $email_token,
            'websiteLink' => 'https://learningplatform.sandbox.9ijakids.com'
        ];

        Mail::to($email)->send(new \App\Mail\VerifyEmail($details));
    }

    private function sendForgotPasswordEmail($email, $forgot_password_token)
    {
        $details = [
            'email' => $email,
            'resetPasswordLink' => 'https://learningplatform.sandbox.9ijakids.com/forgot-password/' . $forgot_password_token,
            'websiteLink' => 'https://learningplatform.sandbox.9ijakids.com'
        ];
        Mail::to($email)->send(new \App\Mail\ForgotPassword($details));
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
        $tel = $this->formatIntlPhoneNo($req->tel);
        $hash = password_hash($req->password, PASSWORD_DEFAULT);
        $token = $this->RandomCode();
        $email_token = $this->RandomCode();

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

            DB::table("users")->where("userID", "=", $id)->update(["companyID" => $companyID]);

            $query = DB::table("users")->where("userEmail", "=", $email)->select(["token"])->get();
            $userData = ["token" => $query[0]->token, "role" => "admin"];
            $this->sendVerifyEmail($firstname, $email, $email_token);
            return response()->json(["success" => true, "data" => $userData, "message" => 'Email sent, please check your inbox']);
        } else {
            return response()->json(["success" => false, "message" => "Company or Admin User Already Exist"], 401);
        }
    }

    public function verifyEmail($email_token)
    {
        if (DB::table('users')->where('email_token', '=', $email_token)->exists()) {
            DB::table("users")->where("email_token", "=", $email_token)->update(["email_token" => "", "verified_status" => "verified"]);
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
            $user = $query[0];
            $pass_ok = password_verify($password, $user->userPassword);
            if ($pass_ok) {
                // if ($email === "nerd2@nimdeetest.com") {
                    //Track Logins
                    // DB::table("login_logs")->insert([ "email" => $email, "message" => "login successful", "status" => 200]);
                    // $userData = ["token" => $user->token, "role" => $user->roleName,];
                    
                // } else {
                    // $token = $this->RandomCode();
                    // DB::table("users")->where("userEmail", "=", $email)->update(["token" => $token]);
                    //Track Logins
                    // DB::table("login_logs")->insert([ "email" => $email, "message" => "login successful", "status" => 200]);
                    // $userData = ["token" => $token, "role" => $user->roleName,];
                // }

                $userData = ["token" => $user->token, "role" => $user->roleName,];
                
                return response()->json(["success" => true, "data" => $userData], 200);
            } else {
                $message = 'Invalid email or password';
                DB::table("login_logs")->insert([ "email" => $email, "message" => $message, "status" => 401]);
                return response()->json(["success" => false, "message" => $message], 401);
            }
        } else {
            $message = 'User not registered';
                DB::table("login_logs")->insert([ "email" => $email, "message" => $message, "status" => 404]);
            return response()->json(["success" => false, "message" => $message], 404);
        }
    }

    public function forgotPassword(Request $req)
    {
        $email = $req->email;
        $forgot_password_token = $this->RandomCode();
        // $userExists = DB::table('users')->where('userEmail', '=', $email);
        if (DB::table('users')->where('userEmail', '=', $email)->exists()) {
            DB::table('users')->where('userEmail', '=', $email)->update(["forgot_password_token" => $forgot_password_token]);
            $this->sendForgotPasswordEmail($email, $forgot_password_token);
            return response()->json(["success" => true, "message" => "An email has been sent to you."]);
        } else {
            return response()->json(["success" => false, "message" => "Users does not exist"], 400);
        }
    }

    public function updateForgotPassword(Request $req)
    {
        $token = $req->token;
        $newPassword = $req->newPassword;

        if (DB::table('users')->where('forgot_password_token', '=', $token)->exists()) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            DB::table("users")->where("forgot_password_token", "=", $token)->update(["userPassword" => $hash, "forgot_password_token" => ""]);
            return response()->json(["success" => true, "message" => "password reset successful"]);
        } else {
            return response()->json(["success" => false, "message" => "Link has expired please login"], 400);
        }
    }
}