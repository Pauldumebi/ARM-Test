<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function createCompanyUser(Request $req)
    {
        $token = $req->token;
        $firstname = $req->firstName;
        $lastname = $req->lastname;
        $email = $req->email;
        $email_suffix = explode("@", $req->email)[1];
        $tel = $req->tel;
        $hash = password_hash("LearningPlatform", PASSWORD_DEFAULT);

        try {
            if (DB::table("users")->where("userEmail", "=", $email)->doesntExist()) {
                $query = DB::table("users")->join("company", "users.companyID", "=", "company.companyID")->select(["company.emailSuffix", "company.companyID"])->where("users.token", "=", $token)->get();

                if ($query[0]->emailSuffix === $email_suffix) {
                    $companyID = $query[0]->companyID;
                    DB::table("users")->insert(["userFirstName" => $firstname, "userLastName" => $lastname, "userEmail" => $email, "userPhone" => $tel, "userPassword" => $hash, "userRoleID" => 2, "companyID" => $companyID]);
                    return response()->json(["success" => true, "message" => "User Account Created"]);
                } else {
                    return response()->json(["success" => false, "message" => "User Email not Company Email"], 401);
                }
            } else {
                return response()->json(["success" => false, "message" => "User Already Registered"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function getCompanyUsers(Request $req)
    {
        $token = $req->token;
        try {
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }
}
