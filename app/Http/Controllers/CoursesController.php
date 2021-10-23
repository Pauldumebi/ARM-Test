<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseFormatSame;

class CoursesController extends Controller
{
    private function assignedACourse($email, $firstname)
    {
        $details = [
            'name' => $firstname,
            'email' => $email,
            'websiteLink' => 'https://learningplatform.sandbox.9ijakids.com'
        ];
        Mail::to($email)->send(new \App\Mail\AssignedACourse($details));
    }

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

    private function userExists($token, $companyID)
    {
        // Checks if token has a corresponding user in the DB and return the userID and companyID
        if (DB::table("users")->where("token", "=", $token)->where("userRoleID", "=", 2)->where("companyID", "=", $companyID)->exists()) {
            $user = DB::table("users")->where("token", "=", $token)->get();
            return ["userExists" => true, "companyID" => $user[0]->companyID, "userID" => $user[0]->userID, "userFirstName" => $user[0]->userFirstName, "userEmail" => $user[0]->userEmail];
        } else {
            return ["userExists" => false];
        }
    }


    public function getCourses()
    {

        // the IF statement is used to return true for instances where the published column is 1 and false if its 0
        $courses = DB::table("course")->selectRaw("courseID, courseName, courseDescription, image, courseCategory, created_at, published
    ")->get();

        if (count($courses) > 0) {
            $bundles = DB::table("courseBundle")->join("bundle", "courseBundle.bundleID", "=", "bundle.bundleID")->select(["courseBundle.bundleID", "bundle.bundleTitle", "bundle.bundleDescription", "bundle.price", "bundle.created_at"])->selectRaw("COUNT(courseBundle.courseID) AS CourseCount")->groupBy("courseBundle.bundleID")->get();

            return response()->json(["success" => true, "courses" => $courses, "bundles" => $bundles]);
        } else {
            return response()->json(["success" => true, "courses" => [], "message" => "No Courses Found"]);
        }
    }

    public function enrolToCourse(Request $req)
    {
        $token = $req->token;
        $usertoken = $req->usertoken;
        $courseID = $req->courseID;

        $checkToken = $this->isAdmin($token);
        // Checks if the token belongs to an company Admin User
        if ($checkToken["isAdmin"]) {
            $checkUser =  $this->userExists($usertoken, $checkToken["companyID"]);
            // Checks if user exists for that company
            if ($checkUser["userExists"]) {

                // Check if course exists
                if (DB::table("course")->where("courseID", "=", $courseID)->exists()) {
                    $userID = $checkUser["userID"];

                    // Checks if user is already enrolled
                    if (DB::table("courseEnrolment")->where("userID", "=", $userID)->where("courseID", "=", $courseID)->doesntExist()) {

                        $companyID = $checkUser["companyID"];

                        $seats = $this->getSeats($companyID, $courseID);
                        // Check if there are available seats
                        if ($seats["Assigned"] < $seats["Total"]) {
                            $this->assignedACourse($checkUser["userFirstName"], $checkUser["userEmail"]);

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
                return response()->json(["success" => false, "message" => "Users to be enrolled does not exist"], 400);
            }
        } else {
            return response()->json(["success" => false, "message" => "User Not Admin"], 401);
        }
    }

    public function unEnrolFromCourse(Request $req)
    {
        $token = $req->token;
        $usertoken = $req->usertoken;
        $courseID = $req->courseID;

        $checkToken = $this->isAdmin($token);
        // Checks if the token belongs to an company Admin User
        if ($checkToken["isAdmin"]) {
            $checkUser =  $this->userExists($usertoken, $checkToken["companyID"]);
            // Checks if user exists for that company
            if ($checkUser["userExists"]) {
                // Check if course exists
                if (DB::table("course")->where("courseID", "=", $courseID)->exists()) {
                    $adminUserID = $checkToken["userID"];
                    $userID = $checkUser["userID"];

                    // Check if user is enrolled in the course
                    if (DB::table("courseEnrolment")->where("userID", "=", $userID)->where("courseID", "=", $courseID)->exists()) {

                        $query = DB::table("courseEnrolment")->where("userID", "=", $userID)->where("courseID", "=", $courseID)->get();
                        // Checks if user is in a Group and delete from group
                        if ($query[0]->groupID != null) {
                            DB::table("userGroup")->where("userID", "=", $userID)->where("groupID", "=", $query[0]->groupID)->delete();
                        }

                        DB::table("courseEnrolment")->where("userID", "=", $userID)->delete();
                        return response()->json(["success" => true, "message" => "Unenrolled Successfully"]);
                    } else {
                        return response()->json(["success" => false, "message" => "Not enrolled in course"], 400);
                    }
                } else {
                    return response()->json(["success" => false, "message" => "Course does not exist"], 400);
                }
            } else {
                return response()->json(["success" => false, "message" => "Users to be enrolled does not exist"], 400);
            }
        } else {
            return response()->json(["success" => false, "message" => "User not admin"], 401);
        }
    }

    public function enrolCompanyToCourse(Request $req)
    {
        $token = $req->token;
        $courseID = $req->courseID;
        // $seats = $req->seats;
        $seats = isset($req->seats) ? $req->seats : 1;

        $checkToken = $this->isAdmin($token);
        // Checks if the token belongs to an company Admin User
        if ($checkToken["isAdmin"]) {

            // Checks if course exists
            if (DB::table("course")->where("courseID", "=", $courseID)->exists()) {
                $userID = $checkToken["userID"];
                $companyID = $checkToken["companyID"];

                // Checks if company is already enrolled in the course
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
            return response()->json(["success" => false, "message" => "User not Admin"], 401);
        }
    }

    public function getEnrolledCourses(Request $req)
    {
        $token = $req->token;


        $query = DB::table("courseEnrolment")->join("course", "courseEnrolment.courseID", "=", "course.courseID")->join("users", "courseEnrolment.userID", "=", "users.userID")->select(["course.courseID", "course.courseName", "course.courseDescription", "course.duration", "course.courseType", "courseEnrolment.created_at"])->where("users.token", "=", $token)->get();

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

        $checkToken = $this->isAdmin($token);
        // Checks if the token belongs to an company Admin User
        if ($checkToken["isAdmin"]) {
            $companyID = $checkToken["companyID"];


            $seats = $this->getSeats($companyID, $courseID);

            return response()->json(["success" => true, "data" => ["Totals Seats" => $seats["Total"], "Assigned Seats" => $seats["Assigned"]]]);
        } else {
            return response()->json(["success" => false, "message" => "User not Admin"], 401);
        }
    }

    public function getCoursesAssignment(Request $req)
    {
        // Think this should only contain courses
        $token = $req->token;

        $checkToken = $this->isAdmin($token);
        // Checks if the token belongs to an company Admin User
        if ($checkToken["isAdmin"]) {

            $courses = DB::table("course")->get();

            if (count($courses) > 0) {
                $i = 0;
                foreach ($courses as $course) {
                    if (DB::table("courseEnrolment")->join("course", "courseEnrolment.courseID", "=", "course.courseID")->join("users", "courseEnrolment.userID", "=", "users.userID")->select(["course.courseID", "course.courseName", "course.courseDescription", "course.duration", "course.courseType", "courseEnrolment.created_at"])->where("users.token", "=", $token)->where("course.courseID", "=", $course->courseID)->exists()) {
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

    public function courseTrackerLog($token)
    {
        $user = DB::table("users")->where("token", "=", $token)->get();
        if (count($user) === 1) {
            $query = DB::table("courseTrackerLog")->join("users", "courseTrackerLog.userID", "users.userID")->join("topic", "courseTrackerLog.topicID", "=", "topic.topicID")->join("module", "topic.moduleID", "=", "module.moduleID")->where("users.token", "=", $token)->get();
            if (count($query) > 0) {
                return response()->json(["success" => true, "courseTrackerLog" => $query]);
            } else {
                return response()->json(["success" => true, "courseTrackerLog" => [], "message" => "No data found"]);
            }
        } else {
            return response()->json(["success" => false, "message" => "User not found"]);
        }
    }

    public function getEnrolledCourseUsers(Request $req)
    {
        $token = $req->token;
        $courseID = $req->courseID;

        $checkToken = $this->isAdmin($token);
        // Checks if the token belongs to an company Admin User
        if ($checkToken["isAdmin"]) {

            if (DB::table("course")->where("courseID", "=", $courseID)->exists()) {

                $users = DB::table("courseEnrolment")->join("users", "users.userID", "=", "courseEnrolment.userID")->where("courseEnrolment.courseID", "=", $courseID)->where("users.companyID", "=", $checkToken["companyID"])->select(["users.userFirstName", "users.userLastName", "users.token", "users.userEmail"])->get();

                if (count($users) > 0) {
                    return response()->json(["success" => true, "users" => $users]);
                } else {
                    return response()->json(["success" => true, "message" => "No users found", "users" => []]);
                }
            } else {
                return response()->json(["success" => false, "message" => "Course does not exists"], 401);
            }
        } else {
            return response()->json(["success" => false, "message" => "User not Admin"], 401);
        }
    }
}
