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
        $courses = DB::table("course")->selectRaw("courseID, courseName, courseDescription, image, courseCategory, created_at, published")->get();

        if (count($courses) > 0) {
            $bundles = DB::table("courseBundle")->join("bundle", "courseBundle.bundleID", "=", "bundle.bundleID")->selectRaw("any_value(courseBundle.bundleID) as bundleID , any_value(courseBundle.courseID) as courseID,
            any_value(bundle.bundleTitle) as bundleTitle, any_value(bundle.bundleDescription) as bundleDescription, 
            any_value(bundle.price) as price, any_value(bundle.created_at) as created_at,
            COUNT(courseBundle.courseID) AS CourseCount")->groupBy("courseBundle.bundleID")->get();

            return response()->json(["success" => true, "courses" => $courses, "bundles" => $bundles]);
        } else {
            return response()->json(["success" => true, "courses" => [], "message" => "No Courses Found"]);
        }
    }
    public function enrollment($token, $usertoken, $courseID){
        $checkToken = $this->isAdmin($token);
        // Checks if the token belongs to a company Admin User
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

                            return ["success" => true, "message" => "Enrollment successful", "status" => 200];
                        }
                        return ["success" => false, "message" => "No more Course Seats!", "status" => 400];
                    } else {
                        return ["success" => true, "message" => "Already Enrolled"];
                    }
                } else {
                    return ["success" => false, "message" => "Course does not exist", "status" => 400];
                }
            } else {
                return ["success" => false, "message" => "Users to be enrolled does not exist", "status" => 400];
            }
        } else {
            return ["success" => false, "message" => "User Not Admin", "status" => 401];
        }
    }
    public function enrolToCourse(Request $req)
    {
        $token = $req->token;
        $usertoken = $req->usertoken;
        $courseID = $req->courseID;

        $response = $this->enrollment($token, $usertoken, $courseID);
        return response()->json(["success" => $response["success"], "message" => $response["message"]], $response["status"]);
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
        $query = DB::table("courseEnrolment")
        ->join("module", "module.courseID","=", "courseEnrolment.courseID")
        ->join("users", "courseEnrolment.userID", "=", "users.userID")
        ->join("course", "courseEnrolment.courseID", "=", "course.courseID")
        // ->leftJoin("courseTrackerLog", "courseTrackerLog.moduleID", "=", "module.moduleID")
        // count(moduleName) as no_of_modules, SUM(courseTrackerLog.status = 'pass') as modules_completed, 
        ->selectRaw("course.courseID, course.courseName, course.courseDescription, course.duration, course.courseType, count(moduleName) as no_of_modules, any_value(users.userID) as userID, any_value(courseEnrolment.created_at) as created_at")->where("users.token", "=", $token)
        ->groupBy("module.courseID")
        ->get();

        foreach ($query as $row) {
            $courseID = $row->courseID;
            $userID = $row->userID;
            $query2 = DB::table("courseTrackerLog")
            ->join("module", "courseTrackerLog.moduleID", "=", "module.moduleID")
            ->join("course", "module.courseID", "=", "course.courseID")
            ->selectRaw("count(courseTrackerLog.status = 'pass') as modules_completed")->where("userID", "=", $userID)->where("course.courseID", "=", $courseID)->get();

            $row->modules_completed=$query2[0]->modules_completed;
        }

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

            return response()->json(["success" => true, "courseName" => $courseName, "modules" => $modules]);
        } else {
            return response()->json(["success" => false, "message" => "Course Does not Exist"], 400);
        }
    }

    public function getCourseModuleTopicsForLoggedInUsers(Request $req)
    {
        $courseID = $req->courseID;
        $token = $req->token;
        $user = DB::table("users")->where("token", "=", $token)->get();
        if (count($user)) {
            $userID = $user[0]->userID;
            $query = DB::table("course")->where("courseID", "=", $courseID)->get();
            // var_dump($query);
            if (count($query) > 0) {
                $courseName = $query[0]->courseName;
                $modules = DB::table("module")->where("courseID", "=", $courseID)->get();

                if (!$modules) {
                    return response()->json(["success" => true, "courseName" => $courseName, "modules" => "No modules for this course"]);
                }

                foreach ($modules as $module) {
                    $moduleID = $module->moduleID;
                    $modulesCompleted = DB::table("courseTrackerLog")->where("moduleID", "=", $moduleID)->where("status", "=", 'pass')->where("userID", "=", $userID)->get();

                    if (count($modulesCompleted) > 0) {
                        $module->status=$modulesCompleted[0]->status;
                    }
                    else 
                        $module->status="null";
                }
                return response()->json(["success" => true, "courseName" => $courseName, "modules" => $modules]);
            } else {
                return response()->json(["success" => false, "message" => "Course Does not Exist"], 400);
            }
        }else 
        return response()->json(["success" => false, "message" => "User not logged in"], 401);
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

    // public function courseTrackerLog($token)
    // {
    //     $user = DB::table("users")->where("token", "=", $token)->get();
    //     if (count($user) === 1) {
    //         $query = DB::table("courseTrackerLog")->join("users", "courseTrackerLog.userID", "users.userID")->join("module", "topic.moduleID", "=", "module.moduleID")->where("users.token", "=", $token)->get();
    //         if (count($query) > 0) {
    //             return response()->json(["success" => true, "courseTrackerLog" => $query]);
    //         } else {
    //             return response()->json(["success" => true, "courseTrackerLog" => [], "message" => "No data found"]);
    //         }
    //     } else {
    //         return response()->json(["success" => false, "message" => "User not found"]);
    //     }
    // }

    // public function getCourseTrackerLog (Request $req) {
    //     $token = $req->token;
    //     $query = DB::table("courseEnrolment")->join("course", "courseEnrolment.courseID", "=", "course.courseID")->join("users", "courseEnrolment.userID", "=", "users.userID")->select(["course.courseID", "course.courseName", "course.courseDescription", "course.duration", "course.courseType", "courseEnrolment.created_at"])->where("users.token", "=", $token)->get();
    //     $query = DB::table("courseTrackerLog")->join("users", "courseTrackerLog.userID", "users.userID")->join("module", "topic.moduleID", "=", "module.moduleID")->where("users.token", "=", $token)->get();
    //     if (count($query) > 0) 
    //         return response()->json(["success" => true, "message" => $query]);
    //     else 
    //     return response()->json(["success" => false, "message" => "no table found"], 204);
    // }

    public function insertCourseTracker (Request $req) {
        $token = $req->token;
        $moduleID = $req->moduleID;
        $user = DB::table("users")->where("token", "=", $token)->get();
        if (count($user) === 1) {
            $userID = $user[0]->userID;
            DB::table("courseTrackerLog")->insert(["userID" => $userID, "moduleID" => $moduleID]);
            return response()->json(["success" => true, "message" => "successfully inserted"]);
        } else
            return response()->json(["success" => false, "message" => "User not found"], 404);
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

    public function recommendCourses(Request $req){
        $token = $req->token;
        $usertoken = $req->usertoken;
        $courseID = $req->courseID;

        $checkToken = $this->isAdmin($token);
        // Checks if the token belongs to a company Admin User
        if ($checkToken["isAdmin"]) {
            $checkUser =  $this->userExists($usertoken, $checkToken["companyID"]);
        
            $query=DB::table("groupRole")->where("groupRoleId", "=", $groupRoleId)->get();

            if(count($query)> 0){
                $course=DB::table('course')->where('courseID',"=",$courseID)->select('courseID','courseName','courseDescription','image')->limit(3)->get();
                return response()->json([ "success" => true, "message"=> "Some Recommended courses you might like"]);

            }
            else{
                return response()->json(["success"=>false, "message"=> "User not an admin and can't recommend courses "]);
            }
    }
}
}