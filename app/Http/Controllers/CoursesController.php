<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoursesController extends Controller
{

    private function getSeats($companyID, $courseID)
    {
        $query = DB::table("courseSeat")->where("companyID", "=", $companyID)->where("courseID", "=", $courseID)->get();
        if (count($query) > 0) {
            $totalSeats = $query[0]->seats;
        } else {
            $totalSeats = 0;
        }


        $query = DB::table("courseEnrolment")->join("users", "courseEnrolment.userID", "=", "users.userID")->where("companyID", "=", $companyID)->where("courseID", "=", $courseID)->get();
        $assignedSeats = count($query);

        return ["Total" => $totalSeats, "Assigned" => $assignedSeats];
    }

    public function getCourses()
    {

        $courses = DB::table("course")->get();

        if (count($courses) > 0) {
            $bundles = DB::table("courseBundle")->join("bundle", "courseBundle.bundleID", "=", "bundle.bundleID")->select(["courseBundle.bundleID", "bundle.bundleTitle", "bundle.bundleDescription", "bundle.price", "bundle.createDate"])->selectRaw("COUNT(courseBundle.courseID) AS CourseCount")->groupBy("courseBundle.bundleID")->get();

            return response()->json(["success" => true, "courses" => $courses, "bundles" => $bundles]);
        } else {
            return response()->json(["success" => true, "courses" => [], "message" => "No Courses Found"]);
        }
    }

    public function enrolToCourse(Request $req)
    {
        $token = $req->token;
        $courseID = $req->courseID;


        $user = DB::table("users")->where("token", "=", $token)->where("userRoleID", "=", 2)->get();
        if (count($user) === 1) {

            if (DB::table("course")->where("courseID", "=", $courseID)->exists()) {
                $userID = $user[0]->userID;

                if (DB::table("courseEnrolment")->where("userID", "=", $userID)->where("courseID", "=", $courseID)->doesntExist()) {

                    $companyID = $user[0]->companyID;

                    $seats = $this->getSeats($companyID, $courseID);

                    if ($seats["Assigned"] < $seats["Total"]) {
                        DB::table("courseEnrolment")->insert(["courseID" => $courseID, "userID" => $userID]);

                        return response()->json(["success" => true, "message" => "Enrollment Successful"]);
                    }

                    return response()->json(["success" => false, "message" => "No more Course Seats!"], 400);
                } else {
                    return response()->json(["success" => true, "message" => "Already Enrolled"]);
                }
            } else {
                return response()->json(["success" => false, "message" => "Course does not exist"], 400);
            }
        } else {
            return response()->json(["success" => false, "message" => "Users does not exist"], 400);
        }
    }

    public function enrolCompanyToCourse(Request $req)
    {
        $token = $req->token;
        $courseID = $req->courseID;
        // $seats = $req->seats;
        $seats = isset($req->seats) ? $req->seats : 1;


        $user = DB::table("users")->where("token", "=", $token)->where("userRoleID", "=", 1)->get();
        if (count($user) === 1) {

            if (DB::table("course")->where("courseID", "=", $courseID)->exists()) {
                $userID = $user[0]->userID;
                $companyID = $user[0]->companyID;

                if (DB::table("courseEnrolment")->where("userID", "=", $userID)->where("courseID", "=", $courseID)->doesntExist()) {

                    DB::table("courseSeat")->insert(["courseID" => $courseID, "companyID" => $companyID, "seats" => $seats]);
                    DB::table("courseEnrolment")->insert(["courseID" => $courseID, "userID" => $userID]);

                    return response()->json(["success" => true, "message" => "Enrollment Successful"]);
                } else {
                    return response()->json(["success" => true, "message" => "Already Enrolled"]);
                }
            } else {
                return response()->json(["success" => false, "message" => "Course does not exist"]);
            }
        } else {
            return response()->json(["success" => false, "message" => "Users does not exist"]);
        }
    }

    public function getEnrolledCourses(Request $req)
    {
        $token = $req->token;


        $query = DB::table("courseEnrolment")->join("course", "courseEnrolment.courseID", "=", "course.courseID")->join("users", "courseEnrolment.userID", "=", "users.userID")->select(["course.courseID", "course.courseName", "course.courseDescription", "course.duration", "course.courseType", "courseEnrolment.enrolDate"])->where("users.token", "=", $token)->get();

        if (count($query) > 0) {
            return response()->json(["success" => true, "enrolledCourses" => $query]);
        } else {
            return response()->json(["success" => true, "enrolledCourses" => [], "message" => "No Enrolled Courses"]);
        }
    }

    public function getCourseModuleTopics(Request $req)
    {
        $courseID = $req->courseID;


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
    }

    public function getCourseSeats(Request $req)
    {
        $token = $req->token;
        $courseID = $req->courseID;


        $user = DB::table("users")->where("token", "=", $token)->where("userRoleID", "=", 1)->get();
        if (count($user) === 1) {
            $companyID = $user[0]->companyID;


            $seats = $this->getSeats($companyID, $courseID);

            return response()->json(["success" => true, "data" => ["Totals Seats" => $seats["Total"], "Assigned Seats" => $seats["Assigned"]]]);
        } else {
            return response()->json(["success" => false, "message" => "Users not Admin"], 401);
        }
    }

    public function getCoursesAssignment(Request $req)
    {
        // Think this should only contain courses
        $token = $req->token;

        $user = DB::table("users")->where("token", "=", $token)->where("userRoleID", "=", 1)->get();
        if (count($user) === 1) {
            $courses = DB::table("course")->get();

            if (count($courses) > 0) {
                $i = 0;
                foreach ($courses as $course) {
                    if (DB::table("courseEnrolment")->join("course", "courseEnrolment.courseID", "=", "course.courseID")->join("users", "courseEnrolment.userID", "=", "users.userID")->select(["course.courseID", "course.courseName", "course.courseDescription", "course.duration", "course.courseType", "courseEnrolment.enrolDate"])->where("users.token", "=", $token)->where("course.courseID", "=", $course->courseID)->exists()) {
                        $course->enrolled = true;
                    } else {
                        $course->enrolled = false;
                    }
                }
                return response()->json(["success" => true, "courses" => $courses]);
            } else {
                return response()->json(["success" => true, "message" => "No Courses Found"]);
            }
        } else {
            return response()->json(["success" => false, "message" => "Users Not Admin"]);
        }
    }
}
