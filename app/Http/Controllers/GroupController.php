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
            return ["isSeatAvailable" => true, "availableSeat" => $totalSeats - $assignedSeats];
        } else {
            return ["isSeatAvailable" => false];
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
        $groupid = $req->groupid;

        try {
            $checkToken = $this->isAdmin($token);
            // Checks if the token belongs to an company Admin User
            if ($checkToken["isAdmin"]) {
                // Checks if the GroupId does exists for the Admin company
                if (DB::table("group")->where("groupID", "=", $groupid)->where("companyID", "=", $checkToken["companyID"])->exists()) {
                    if (DB::table("groupEnrolment")->where("groupID", "=", $groupid)->doesntExist()) {
                        DB::table("userGroup")->where("groupID", "=", $groupid)->delete();
                        DB::table("group")->where("groupID", "=", $groupid)->delete();
                        return response()->json(["success" => true, "message" => "Group Removed Successfully"]);
                    } else {
                        DB::table("courseEnrolment")->where("groupid", "=", $groupid)->delete();
                        DB::table("groupEnrolment")->where("groupid", "=", $groupid)->delete();
                        DB::table("userGroup")->where("groupID", "=", $groupid)->delete();
                        DB::table("group")->where("groupID", "=", $groupid)->delete();
                        return response()->json(["success" => true, "message" => "Group Removed Successfully"]);
                    }
                } else {
                    return response()->json(["success" => false, "message" => "Group does not Exist"], 400);
                }
            } else {
                return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function fetchCompanyGroup($token)
    {

        try {
            $checkToken = $this->isAdmin($token);
            // Checks if the token belongs to an company Admin User
            if ($checkToken["isAdmin"]) {
                $groups = DB::table("group")->where("companyID", "=", $checkToken["companyID"])->select(["groupID", "groupName", "create_date"])->get();
                // Checks if the a company has ay groups at all
                if (count($groups) > 0) {
                    return response()->json(["success" => true, "data" => $groups]);
                } else {
                    return response()->json(["success" => false, "message" => "No Group for Company"], 400);
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
        $courseid = $req->courseID;
        $groupid = $req->groupid;

        try {
            $checkToken = $this->isAdmin($token);
            // Checks if the token belongs to an company Admin User
            if ($checkToken["isAdmin"]) {
                // Check if the group id matches a group for the Admin's company
                if (DB::table("group")->where("groupID", "=", $groupid)->where("companyID", "=", $checkToken["companyID"])->exists()) {
                    // Check if course id has been enrolled to by the company admin
                    if (DB::table("courseEnrolment")->where("courseID", "=", $courseid)->where("userID", "=", $checkToken["userID"])->exists()) {
                        // Checks if the group is already assigned to the course
                        if (DB::table("groupEnrolment")->where("courseID", "=", $courseid)->where("groupID", "=", $groupid)->doesntExist()) {
                            // Get Available Seats
                            $isSeatAvailable = $this->isSeatAvailable($checkToken["companyID"], $courseid);
                            // Checks if there are no seats in Course
                            if (!$isSeatAvailable["isSeatAvailable"]) {
                                return response()->json(["success" => false, "message" => "No more Seats in this course"], 400);
                            } else {
                                // Get Users attached to Group
                                $users = DB::table("userGroup")->where("groupID", "=", $groupid)->get();
                                // Check if available course seats is more than or equal to users in the group
                                if ($isSeatAvailable["availableSeat"] >= count($users)) {
                                    DB::table("groupEnrolment")->insert(["courseID" => $courseid, "groupID" => $groupid]);
                                    if (count($users) > 0) {
                                        foreach ($users as $user) {
                                            DB::table("courseEnrolment")->updateOrInsert(["userID" => $user->userID, "courseID" => $courseid], ["groupID" => $groupid]);
                                        }
                                    }
                                    return response()->json(["success" => true, "message" => "Group Assigned Successfully"]);
                                } else {
                                    return response()->json(["success" => false, "message" => "Not Enough Course Seats for Group"], 400);
                                }
                            }
                        } else {
                            return response()->json(["success" => false, "message" => "Group Already Assigned to course"], 400);
                        }
                    } else {
                        return response()->json(["success" => false, "message" => "Course not Enrolled for"], 400);
                    }
                } else {
                    return response()->json(["success" => false, "message" => "Group does not exist"], 400);
                }
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
        $courseid = $req->courseID;
        $groupid = $req->groupid;

        try {
            $checkToken = $this->isAdmin($token);
            // Checks if the token belongs to an company Admin User
            if ($checkToken["isAdmin"]) {
                // Check if the group id matches a group for the Admin's company
                if (DB::table("group")->where("groupID", "=", $groupid)->where("companyID", "=", $checkToken["companyID"])->exists()) {

                    // Checks if the group is really assigned to the course
                    if (DB::table("groupEnrolment")->where("courseID", "=", $courseid)->where("groupID", "=", $groupid)->exists()) {

                        DB::table("courseEnrolment")->where("groupid", "=", $groupid)->where("courseID", "=", $courseid)->delete();

                        DB::table("groupEnrolment")->where("groupid", "=", $groupid)->where("courseID", "=", $courseid)->delete();
                        return response()->json(["success" => true, "message" => "Unassignment Successful"]);
                    } else {
                        return response()->json(["success" => false, "message" => "Course not assigned to group"], 400);
                    }
                } else {
                    return response()->json(["success" => false, "message" => "Group does not exist"], 400);
                }
            } else {
                return response()->json(["success" => false, "message" => "Users Not Admin"], 400);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function addUser(Request $req)
    {
        $token = $req->token;
        $usertoken = $req->usertoken;
        $groupid = $req->groupid;

        try {
            $checkToken = $this->isAdmin($token);
            // Check if token is that of a Company Admin User
            if ($checkToken["isAdmin"]) {
                $userExists = $this->userExists($usertoken, $checkToken["companyID"]);
                // Check if the user to be added to a group exists for the Admin Company
                if ($userExists["userExists"]) {
                    // Check if the group id matches a group for the Admin's company
                    if (DB::table("group")->where("groupID", "=", $groupid)->where("companyID", "=", $checkToken["companyID"])->exists()) {
                        // Check if the group has NOT been assigned to a course already
                        if (DB::table("groupEnrolment")->where("groupID", "=", $groupid)->doesntExist()) {
                            DB::table("userGroup")->updateOrInsert(["userID" => $userExists["userID"], "groupID" => $groupid]);
                            return response()->json(["success" => true, "message" => "User Added Successfully"]);
                        } else {
                            $courses = DB::table("groupEnrolment")->join("course", "course.courseID", "=", "groupEnrolment.courseID")->where("groupID", "=", $groupid)->get();
                            $i = -1;
                            // Loop through all courses attached to a group and checks if they have available seats for the new user to be added
                            foreach ($courses as $course) {
                                $i++;
                                $coursesNoSeat = [];
                                // Get Available Seats
                                $isSeatAvailable = $this->isSeatAvailable($checkToken["companyID"], $course->courseID);
                                $result[$i] = $isSeatAvailable["isSeatAvailable"];
                                // Checks if seat is not available for a course and adds it to the 'coursesNoSeat' array
                                if (!$isSeatAvailable["isSeatAvailable"]) {
                                    array_push($coursesNoSeat, $course->courseName);
                                }
                            }
                            // Checks if any of the courses attached to the Group does not have enought seats
                            if (in_array(false, $result)) {
                                return response()->json(["success" => false, "message" => "No more Seats in Course(s) assigned to Group", "coursesNoSeats" => $coursesNoSeat], 400);
                            } else {
                                DB::table("userGroup")->updateOrInsert(["userID" => $userExists["userID"], "groupID" => $groupid]);

                                // Loops through the courses attached to the Group and assigns them to the user
                                foreach ($courses as $course) {
                                    DB::table("courseEnrolment")->updateOrInsert(["userID" => $userExists["userID"], "courseID" => $course->courseID], ["groupID" => $groupid]);
                                }
                                return response()->json(["success" => true, "message" => "User Added Successfully"]);
                            }
                        }
                    } else {
                        return response()->json(["success" => false, "message" => "Group does not exist"], 400);
                    }
                } else {
                    return response()->json(["success" => false, "message" => "Users does not exist"], 400);
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
        $usertoken = $req->usertoken;
        $groupid = $req->groupid;

        try {
            $checkToken = $this->isAdmin($token);
            if ($checkToken["isAdmin"]) {
                $userExists = $this->userExists($usertoken, $checkToken["companyID"]);
                // Check if the user to be added to a group exists for the Admin Company
                if ($userExists["userExists"]) {
                    // Check if the group id matches a group for the Admin's company
                    if (DB::table("group")->where("groupID", "=", $groupid)->where("companyID", "=", $checkToken["companyID"])->exists()) {
                        // Check if the group has NOT been assigned to a course already
                        if (DB::table("groupEnrolment")->where("groupID", "=", $groupid)->doesntExist()) {
                            DB::table("userGroup")->where("userID", "=", $userExists["userID"])->where("groupID", "=", $groupid)->delete();
                            return response()->json(["success" => true, "message" => "User Removed Successfully"]);
                        } else {
                            DB::table("courseEnrolment")->where("userID", "=", $userExists["userID"])->where("groupID", "=", $groupid)->delete();
                            DB::table("userGroup")->where("userID", "=", $userExists["userID"])->where("groupID", "=", $groupid)->delete();
                            return response()->json(["success" => true, "message" => "User Removed Successfully"]);
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

    public function fetchGroupUser(Request $req)
    {

        $token = $req->token;
        $groupid = $req->groupid;

        try {
            $checkToken = $this->isAdmin($token);
            if ($checkToken["isAdmin"]) {
                // Check if group exist for that particular company
                if (DB::table("group")->where("groupID", "=", $groupid)->where("companyID", "=", $checkToken["companyID"])->exists()) {
                    $user = DB::table("userGroup")->join("users", "users.userID", "=", "userGroup.userID")->where("groupID", "=", $groupid)->select(["userFirstName", "userLastName", "token"])->get();
                    // Check if there are users for the group
                    if (count($user) > 0) {
                        return response()->json(["success" => true, "users" => $user]);
                    } else {
                        return response()->json(["success" => false, "message" => "No Users"], 400);
                    }
                } else {
                    return response()->json(["success" => false, "message" => "Group does not exist"], 400);
                }
            } else {
                return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }

    public function fetchGroupCourse(Request $req)
    {

        $token = $req->token;
        $groupid = $req->groupid;

        try {
            $checkToken = $this->isAdmin($token);
            if ($checkToken["isAdmin"]) {
                // Check if group exist for that particular company
                if (DB::table("group")->where("groupID", "=", $groupid)->where("companyID", "=", $checkToken["companyID"])->exists()) {
                    $course = DB::table("groupEnrolment")->join("course", "course.courseID", "=", "groupEnrolment.courseID")->where("groupID", "=", $groupid)->select(["course.courseID", "course.courseName", "groupEnrolment.enrol_date"])->get();
                    // Check if there are courses for the group
                    if (count($course) > 0) {
                        return response()->json(["success" => true, "courses" => $course]);
                    } else {
                        return response()->json(["success" => false, "message" => "No Courses"], 400);
                    }
                } else {
                    return response()->json(["success" => false, "message" => "Group does not exist"], 400);
                }
            } else {
                return response()->json(["success" => false, "message" => "Users Not Admin"], 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()], 500);
        }
    }
}
