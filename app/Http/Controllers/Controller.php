<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getUrl()
    {
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $parts = parse_url($actual_link);
        $scheme = explode('/', $parts['scheme']);
        $host = explode('/', $parts['host']);
        $hostUrl = $scheme[0] . "://" . $host[0];
        return $hostUrl;
    }
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

    public function formatIntlPhoneNo($phone)
    {
        if (substr($phone, 0, 1) === '0') {
            return '234' . substr($phone, 1);
        }
        return $phone;
    }

    
    // public function enrollment($token, $usertoken, $courseID){
    //     $checkToken = $this->isAdmin($token);
    //     // Checks if the token belongs to a company Admin User
    //     if ($checkToken["isAdmin"]) {
    //         $checkUser =  $this->userExists($usertoken, $checkToken["companyID"]);
    //         // Checks if user exists for that company
    //         if ($checkUser["userExists"]) {

    //             // Check if course exists
    //             if (DB::table("course")->where("courseID", "=", $courseID)->exists()) {
    //                 $userID = $checkUser["userID"];

    //                 // Checks if user is already enrolled
    //                 if (DB::table("courseEnrolment")->where("userID", "=", $userID)->where("courseID", "=", $courseID)->doesntExist()) {

    //                     $companyID = $checkUser["companyID"];

    //                     $seats = $this->getSeats($companyID, $courseID);
    //                     // Check if there are available seats
    //                     if ($seats["Assigned"] < $seats["Total"]) {
    //                         $this->assignedACourse($checkUser["userFirstName"], $checkUser["userEmail"]);

    //                         DB::table("courseEnrolment")->insert(["courseID" => $courseID, "userID" => $userID]);

    //                         return ["success" => true, "message" => "Enrollment successful", "status" => 200];
    //                     }
    //                     return ["success" => false, "message" => "No more Course Seats!", "status" => 400];
    //                 } else {
    //                     return ["success" => true, "message" => "Already Enrolled"];
    //                 }
    //             } else {
    //                 return ["success" => false, "message" => "Course does not exist", "status" => 400];
    //             }
    //         } else {
    //             return ["success" => false, "message" => "Users to be enrolled does not exist", "status" => 400];
    //         }
    //     } else {
    //         return ["success" => false, "message" => "User Not Admin", "status" => 401];
    //     }
    // }
}
