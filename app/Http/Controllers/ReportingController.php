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

            $queryForCourses = DB::table("courseEnrolment")->join("course", "courseEnrolment.CourseID", "=", "course.CourseID")->where("companyID", "=", $companyID)->selectRaw("course.courseID, courseName, count(userID) as enrolled")->groupBy("courseID")->get();

            foreach ($queryForCourses as $course) {
                $courseID=$course->courseID;
                $totalEnrolled =$course->enrolled;

                $queryForCompleted = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("status", "=", "pass")->where("courseID", "=", $courseID)->groupBy("courseAssessmentLog.userID")->selectRaw("max(score) as complete")->get();
                
                $averageSum = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("status", "=", "pass")->where("courseID", "=", $courseID)->selectRaw("round(avg(score), 0) as average")->get();

                $complete = count($queryForCompleted);
                $incomplete = $totalEnrolled - $complete;

                $course->complete=$complete;
                $course->incomplete=$incomplete;
                $course->averageSum=$$averageSum;
            }

            return response()->json(["success" => true, "message" => $queryForCourses]);
        }
    }
}
