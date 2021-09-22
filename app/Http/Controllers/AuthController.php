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
                $code .= rand(0, 9); // Numbers!!
            }
            $total++;
        } while ($total < $length);
        return $code;
    }

    // each endpoint will have a function
    public function signup(Request $req)
    {
        $comName = $req->companyName;
        $comAdr = $req->companyAddress;
        $comEmailSuffix = $req->companyEmailSuffix;
        $firstname = $req->firstName;
        $lastname = $req->lastName;
        $email = $req->email;
        $adminRole = $req->adminRole;
        $tel = $req->tel;
        $hash = password_hash($req->password, PASSWORD_DEFAULT);
        $token = $this->RandomCode();



        if (DB::table("users")->join("company", "users.companyID", "=", "company.companyID")->where("users.userEmail", "=", $email)->orWhere("company.companyName", "=", $comName, "or")->orWhere("company.emailSuffix", "=", $comEmailSuffix)->doesntExist()) {

            $id = DB::table("users")->insertGetId(
                ["userFirstName" => $firstname, "userLastName" => $lastname, "userEmail" => $email, "userPhone" => $tel, "userPassword" => $hash, "userRoleID" => 1, "token" => $token],
            );

            $companyID = DB::table("company")->insertGetId([
                "companyName" => $comName,
                "companyAddress1" => $comAdr,
                "companyAdminID" => $id,
                "emailSuffix" => $comEmailSuffix,
                "companyAdminRole" => $adminRole
            ]);

            $updatedID = DB::table("users")->where("userEmail", "=", $email)->update([
                "companyID" => $companyID
            ]);

            $query = DB::table("users")->where("userEmail", "=", $email)->select(["token"])->get();

            $userData = ["token" => $query[0]->token, "role" => "admin"];
            return response()->json(["success" => true, "data" => $userData]);
        } else {
            return response()->json(["success" => false, "message" => "Comapany or Admin User Already Exist"], 401);
        }
        // return response()->json(["success" => true, "data" => $users], 200);

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
}
