<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoursesController extends Controller
{
    public function getCourses()
    {
        try {
            $courses = DB::table("course")->get();

            if (count($courses) > 0) {
                $bundles = DB::table("courseBundle")->join("bundle", "courseBundle.bundleID", "=", "bundle.bundleID")->select(["courseBundle.bundleID", "bundle.bundleTitle", "bundle.bundleDescription", "bundle.price", "bundle.createDate"])->selectRaw("COUNT(courseBundle.courseID) AS CourseCount")->groupBy("courseBundle.bundleID")->get();

                return response()->json(["success" => true, "courses" => $courses, "bundles" => $bundles]);
            } else {
                return response()->json(["success" => true, "message" => "No Courses Found"]);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function enrolToCourse(Request $req)
    {
        $token = $req->token;
        $courseID = $req->courseID;

        try {
            $user = DB::table("users")->where("token", "=", $token)->get();
            if (count($user) === 1) {
                $userID = $user[0]->userID;
                if (DB::table("courseEnrolment")->where("userID", "=", $userID)->where("courseID", "=", $courseID)->doesntExist()) {

                    DB::table("courseEnrolment")->insert(["courseID" => $courseID, "userID" => $userID]);

                    return response()->json(["success" => true, "message" => "Enrollment Successful"]);
                } else {
                    return response()->json(["success" => true, "message" => "Already Enrolled"]);
                }
            } else {
                return response()->json(["success" => false, "message" => "Users does not exist"]);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function getEnrolledCourses(Request $req)
    {
        $token = $req->token;

        try {
            $query = DB::table("courseEnrolment")->join("course", "courseEnrolment.courseID", "=", "course.courseID")->join("users", "courseEnrolment.userID", "=", "users.userID")->select(["course.courseID", "course.courseName", "course.courseDescription", "course.duration", "course.courseType", "courseEnrolment.enrolDate"])->where("users.token", "=", $token)->get();

            if (count($query) > 0) {
                return response()->json(["success" => true, "enrolledCourses" => $query]);
            } else {
                return response()->json(["success" => true, "message" => "No Enrolled Courses"]);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function getCourseModuleTopics(Request $req)
    {
        $courseID = $req->courseID;

        try {
            $query = DB::table("course")->where("courseID", "=", $courseID)->get();
            if (count($query) > 0) {
                $courseName = $query[0]->courseName;
                $modules = DB::table("module")->where("courseID", "=", $courseID)->get();
                $i = -1;
                foreach ($modules as $module) {
                    $i++;
                    $result[$i]["moduleName"] = $module->moduleName;
                    $topics = DB::table("topic")->where("moduleID", "=", $module->moduleID)->select("topicID", "topicName", "duration")->get();
                    $result[$i]["topics"] = $topics;
                }

                return response()->json(["success" => true, "courseName" => $courseName, "modulesTopics" => $result]);
            } else {
                return response()->json(["success" => false, "message" => "Course Does not Exist"], 400);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }
}
