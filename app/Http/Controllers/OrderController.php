<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $token = $req->token;
        $courses = $req->courses;
        $isAdmin = $this->isAdmin($token);
        $alreadyOrdered = [];
        if ($isAdmin["isAdmin"]) {
            foreach ($courses as $course) {
                $orderNumber = $this->OrderID();
                // DB::table("orders")->updateOrInsert(["companyID" => $isAdmin["companyID"], "courseID" => $course["id"]], ["orderNumber" => $orderNumber,  "status" => "pending", "seats" => $course["seats"]]);
                if (DB::table("orders")->where("companyID", "=", $isAdmin["companyID"])->where("courseID", "=", $course["id"])->doesntExist()) {
                    DB::table("orders")->insert(["companyID" => $isAdmin["companyID"], "courseID" => $course["id"], "orderNumber" => $orderNumber,  "status" => "pending", "seats" => $course["seats"]]);
                } else {
                    array_push($alreadyOrdered, "Course with ID " . $course["id"] . " Already Ordered");
                }
            }
            return response()->json(["success" => true, "message" => "Order has been created.", "orderExists" => $alreadyOrdered]);
        } else {
            return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
        }
    }
    public function getOrders($token)
    {
        $isAdmin = $this->isAdmin($token);
        if ($isAdmin["isAdmin"]) {
            $result = DB::table("orders")->join("course", "course.courseID", "=", "orders.courseID")->where("companyID", "=", $isAdmin["companyID"])->get();
            return response()->json(["success" => true, "orders" => $result]);
        } else {
            return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
        }
    }
}
