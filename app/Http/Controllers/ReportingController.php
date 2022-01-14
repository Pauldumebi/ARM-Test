<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    public function allCourses (Request $req) {
        $token = $req->token;
        $query = DB::table("users")->join("role", "users.userRoleID", "=", "role.RoleID")->where("token", "=", $token)->select(["companyID", "roleName"])->get();
        if ($query[0]->roleName === "admin") {
            $companyID = $query[0]->companyID;

            $queryForCourses = DB::table("courseEnrolment")->join("course", "courseEnrolment.CourseID", "=", "course.CourseID")->where("companyID", "=", $companyID)->selectRaw("any_value(companyID) as companyID, course.courseID, courseName, count(userID) as enrolled")->groupBy("courseID")->get();

            foreach ($queryForCourses as $course) {
                $courseID=$course->courseID;
                $totalEnrolled =$course->enrolled;

                $queryForCompleted = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("status", "=", "pass")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->groupBy("courseAssessmentLog.userID")->selectRaw("max(score) as complete")->get();
                
                $averageSum = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("companyID", "=", $companyID)->where("courseID", "=", $courseID)->selectRaw("round(avg(score), 0) as average")->get();

                $averageRange = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("companyID", "=", $companyID)->where("courseID", "=", $courseID)->selectRaw("concat( MIN(score) , '-', MAX(score)) as average_range")->get();

                $complete = count($queryForCompleted);
                $incomplete = $totalEnrolled - $complete;

                $course->complete=$complete;
                $course->incomplete=$incomplete;
                $course->averageSum=$averageSum[0]->average ?: $course->averageSum=null;
                $course->averageRange=$averageRange[0]->average_range ?? $course->averageRange=null;
            }

            return response()->json(["success" => true, "message" => $queryForCourses]);
        }
    }

    public function candidateDetails (Request $req ){
        $token=$req->token;
        $email= $req->email;
        if (DB::table("users")->where("email","=",$email)->exists()) {
            // $queryUserTable = DB::table("users")->where("token", "=", $token)->orWhere("email", "=", $email)->get();
        
            // if (count($queryUserTable) === 1) {
             $query = DB::table("users")->join("role", "users.userRoleID", "=", "role.RoleID")->where("token", "=", $token)->select(["users.userID", "users.userFirstName", "users.userEmail","role.roleName","users.userGrade","users.location","users.userGender"])->get();

             return response()->json(["success" => true, "message" => $query]);
            }     
        else{
                return response()->json(["success" => false, "message" => "User details incorrect"]);
            }
        
    }

    public function candidateTable (Request $req){
        $token=$req->token;
        $userEmail=$req->email;

        if(DB::table('courseEnrolment')->join("users", "courseEnrolment.userID", "=", "users.userID")->where("users.userID","=", "courseEnrolment.usersID"))
        $query=DB::table('course')->join("courseAssessmentLog","courseAssessmentLog.courseID", "=", "course.courseID")->select("courseAssessmentLog.score","courseAssessmentLog.status")->get();

        
        return response()->json(["success" => true, "message" => $query]);

    }
}
