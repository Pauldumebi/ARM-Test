<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{

    private function isAdmin($token)
    {
        // Checks if token has admin priviledges and returns companyID of Admin
        if (DB::table("users")->where("token", "=", $token)->where("userRoleID", "=", 1)->exists()) {
            $user = DB::table("users")->where("token", "=", $token)->get();
            return ["isAdmin" => true, "companyID" => $user[0]->companyID];
        } else {
            return ["isAdmin" => false];
        }
    }

    private function userExists($token)
    {
        // Checks if token has a corresponding user in the DB and return the userID and companyID
        if (DB::table("users")->where("token", "=", $token)->where("userRoleID", "=", 2)->exists()) {
            $user = DB::table("users")->where("token", "=", $token)->get();
            return ["userExists" => true, "companyID" => $user[0]->companyID, "userID" => $user[0]->userID];
        } else {
            return ["userExists" => false];
        }
    }

    private function isSeatAvailable($companyID, $courseID)
    {
        $query = DB::table("courseSeat")->where("companyID", "=", $companyID)->where("courseID", "=", $courseID)->get();
        // Checks if the course has an entry in the 'courseSeats' table
        if (count($query) > 0) {
            $totalSeats = $query[0]->seats;
        } else {
            $totalSeats = 0;
        }


        $query = DB::table("courseEnrolment")->join("users", "courseEnrolment.userID", "=", "users.userID")->where("companyID", "=", $companyID)->where("courseID", "=", $courseID)->get();
        $assignedSeats = count($query);

        // Checks if there are available seats for a particular course for a company
        if ($totalSeats > $assignedSeats) {
            return true;
        } else {
            return false;
        }
    }

    public function createGroup(Request $req)
    {
        $token = $req->token;
        $groupname = $req->groupname;

        try {
            $checkToken = $this->isAdmin($token);
            // Checks if the token belongs to an company Admin User
            if ($checkToken["isAdmin"]) {
                // Checks if the GroupName does not already exists for that company
                if (DB::table("group")->where("groupName", "=", $groupname)->where("companyID", "=", $checkToken["companyID"])->doesntExist()) {
                    DB::table("group")->insert(["groupName" => $groupname, "companyID" => $checkToken["companyID"]]);
                    return response()->json(["success" => true, "message" => "Group Created Successfully"]);
                } else {
                    return response()->json(["success" => false, "message" => "Group Already Exists"], 400);
                }
            } else {
                return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }


    public function removeGroup(Request $req)
    {

        $token = $req->token;

        try {
            $checkToken = $this->isAdmin($token);
            // Checks if the token belongs to an company Admin User
            if ($checkToken["isAdmin"]) {
            } else {
                return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function fetchCompanyGroup(Request $req)
    {

        $token = $req->token;

        try {
            $checkToken = $this->isAdmin($token);
            // Checks if the token belongs to an company Admin User
            if ($checkToken["isAdmin"]) {
                $groups = DB::table("group")->where("companyID", "=", $checkToken["companyID"])->select(["groupID", "groupName", "create_date"])->get();
                // Checks if the a company has ay groups at all
                if (count($groups) > 0) {
                    return response()->json(["success" => true, "data" => $groups]);
                } else {
                    return response()->json(["success" => false, "message" => "No Group for Company"]);
                }
            } else {
                return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function assignCourse(Request $req)
    {

        $token = $req->token;

        try {
            $checkToken = $this->isAdmin($token);
            // Checks if the token belongs to an company Admin User
            if ($checkToken["isAdmin"]) {
            } else {
                return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function unassignCourse(Request $req)
    {

        $token = $req->token;

        try {
            $checkToken = $this->isAdmin($token);
            // Checks if the token belongs to an company Admin User
            if ($checkToken["isAdmin"]) {
            } else {
                return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function addUser(Request $req)
    {
        //Lets test CICD
        $token = $req->token;
        $usertoken = $req->usertoken;
        $groupid = $req->groupid;

        try {
            $checkToken = $this->isAdmin($token);
            // Check if token is that of a Company Admin User
            if ($checkToken["isAdmin"]) {
                $userExists = $this->userExists($usertoken);
                // Check if the user to be added to a group exists
                if ($userExists["userExists"]) {
                    // Check if the group id matches a group for the Admin's company
                    if (DB::table("group")->where("groupID", "=", $groupid)->where("companyID", "=", $checkToken["companyID"])->exists()) {
                        // Check if the group has NOT been assigned to a course already
                        if (DB::table("groupEnrolment")->where("groupID", "=", $groupid)->doesntExist()) {
                            DB::table("userGroup")->upsert(["userID" => $userExists["userID"], "groupID" => $groupid], ["userID", "groupID"]);
                            return response()->json(["success" => true, "message" => "User Added Successfully"]);
                        } else {
                            $courses = DB::table("groupEnrolment")->join("course", "course.courseID", "=", "groupEnrolment.courseID")->where("groupID", "=", $groupid)->get();
                            $i = -1;
                            // Loop through all courses attached to a group and checks if they have available seats for the new user to be added
                            foreach ($courses as $course) {
                                $i++;
                                $coursesNoSeat = [];
                                // $result[$i]["course"] = $course->courseID;
                                $isSeatAvailable = $this->isSeatAvailable($checkToken["companyID"], $course->courseID);
                                $result[$i] = $isSeatAvailable;
                                // Checks if seat is not available for a course and adds it to the 'coursesNoSeat' array
                                if (!$isSeatAvailable) {
                                    array_push($coursesNoSeat, $course->courseName);
                                }
                            }
                            // Checks if any of the courses attached to the Group does not have enought seats
                            if (in_array(false, $result)) {
                                return response()->json(["success" => false, "message" => "No more Seats in Course(s) assigned to Group", "coursesNoSeats" => $coursesNoSeat], 401);
                            } else {
                                DB::table("userGroup")->upsert(["userID" => $userExists["userID"], "groupID" => $groupid], ["userID", "groupID"]);
                                // Loops through the courses attached to the Group and assigns them to the user
                                foreach ($courses as $course) {
                                    DB::table("courseEnrolment")->upsert(["userID" => $userExists["userID"], "courseID" => $course->courseID, "groupID" => $groupid], ["userID", "courseID"], ["groupID"]);
                                }
                                return response()->json(["success" => true, "message" => "User Added Successfully"]);
                            }
                        }
                    } else {
                        return response()->json(["success" => false, "message" => "Group does not exist"], 401);
                    }
                } else {
                    return response()->json(["success" => false, "message" => "Users does not exist"], 401);
                }
            } else {
                return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function removeUser(Request $req)
    {

        $token = $req->token;

        try {
            $checkToken = $this->isAdmin($token);
            if ($checkToken["isAdmin"]) {
            } else {
                return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function fetchGroupUser(Request $req)
    {

        $token = $req->token;

        try {
            $checkToken = $this->isAdmin($token);
            if ($checkToken["isAdmin"]) {
            } else {
                return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }
}
