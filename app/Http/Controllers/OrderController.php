<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
   private function OrderID($length = 6)
    {
        $code = "";
        $total = 0;
        do {
            if (rand(0, 1) == 0) {
                $code .= chr(rand(97, 122)); // ASCII code from **a(97)** to **z(122)**
            } else {
                $code .= rand(0, 6); // Numbers!!
            }
            $total++;
        } while ($total < $length);
        return $code;
    }

    private function isAdmin($token)
    {
        // Checks if token has admin privileges and return companyID of Admin
        if (DB::table("users")->where("token", "=", $token)->where("userRoleID", "=", 1)->exists()) {
            $user = DB::table("users")->where("token", "=", $token)->get();
            return ["isAdmin" => true, "companyID" => $user[0]->companyID, "userID" => $user[0]->userID];
        } else {
            return ["isAdmin" => false];
        }
    }

    public function checkoutOrders(Request $req) 
    { 
        $orderNumber = $this -> OrderID(); 
        $token = $req-> token;
        $courseIDs = $req-> courseIDs;
        $isAdmin = $this-> isAdmin($token);
        if ($isAdmin["isAdmin"]) {
            foreach ($courseIDs as $courseID) {
                DB::table("orders")->insert(["orderNumber" => $orderNumber,"companyID" => $isAdmin["companyID"],"courseID" => $courseID, "status" => "pending"]);
            }
            return response()->json(["success" => true, "message" => "Order has been created."]);
        } else {
            return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
        }
    }
    public function getOrders($token) 
    { 
        $isAdmin = $this-> isAdmin($token);
        if ($isAdmin["isAdmin"]) {
            $result = DB::table("orders")->join("course", "course.courseID", "=", "orders.courseID")->where("companyID", "=", $isAdmin["companyID"])->get(); 
            return response()->json(["success" => true, "orders" => $result]);
        } else {
            return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
        }
    }
}