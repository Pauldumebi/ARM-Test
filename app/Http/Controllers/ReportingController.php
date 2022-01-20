<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    public function filterParams(Request $req)
    {
        $token = $req->token;
        $queryForCompanyId = DB::table("users")->where("token", "=", $token)->select("companyID")->get();
        $companyID = $queryForCompanyId[0]->companyID;
        $location = DB::table("users")->where("companyID", "=", $companyID)->selectRaw("distinct(location)")->get();
        $queryForGrade = DB::table("users")->where("companyID", "=", $companyID)->selectRaw("distinct(userGrade)")->get();
        $queryForRoleName = DB::table("groupRole")->selectRaw("roleName")->get();
        $queryForGroup = DB::table("group")->join("users", "group.companyID", "=", "users.companyID")->where("group.companyID", "=", $companyID)->selectRaw("distinct(groupName)")->get();
        $gender = ["M", "F"];

        return response()->json(["success" => true, "location" => $location, "grade" => $queryForGrade, "roleName" => $queryForRoleName, "group" => $queryForGroup, "gender" => $gender]);
    }

    public function allCourses(Request $req)
    {
        $token = $req->token;
        $userGender = $req->userGender;
        $location = $req->location;
        $roleName = $req->roleName;
        $userGrade = $req->userGrade;
        $query = DB::table("users")->join("role", "users.userRoleID", "=", "role.RoleID")->where("token", "=", $token)->select(["companyID", "roleName"])->get();
        if ($query[0]->roleName === "admin") {
            $companyID = $query[0]->companyID;

            if ($userGender || $location || $userGrade || $roleName) {
                $groupRoleID = null;
                if ($roleName) {
                    $queryForGroupRoleID = DB::table("groupRole")->where("roleName", "=", $roleName)->get();
                    $groupRoleID = $queryForGroupRoleID[0]->groupRoleId;
                }
                $queryForCourses = DB::table("courseEnrolment")->join("course", "courseEnrolment.CourseID", "=", "course.CourseID")->join("users", "courseEnrolment.userID", "=", "users.userID")->where("courseEnrolment.companyID", "=", $companyID)->where("groupRoleID", "like", "%" . $groupRoleID . "%")->where("userGrade", "like", "%" . $userGrade . "%")->where("location", "like", "%" . $location . "%")->where("userGender", "like", "%" . $userGender . "%")->selectRaw("any_value(courseEnrolment.companyID) as companyID, course.courseID, courseName, count(courseEnrolment.userID) as enrolled")->groupBy("courseID")->get();
            } else
                $queryForCourses = DB::table("courseEnrolment")->join("course", "courseEnrolment.CourseID", "=", "course.CourseID")->where("companyID", "=", $companyID)->selectRaw("any_value(companyID) as companyID, course.courseID, courseName, count(userID) as enrolled")->groupBy("courseID")->get();

            if (!$queryForCourses) {
                $queryForCourses = [];
                return response()->json(["success" => true, "message" => $queryForCourses]);
            }

            foreach ($queryForCourses as $course) {
                $courseID = $course->courseID;
                $totalEnrolled = $course->enrolled;

                if ($userGender || $location || $userGrade || $roleName) {
                    $queryForCompleted = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("status", "=", "pass")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->where("groupRoleID", "like", "%" . $groupRoleID . "%")->where("userGrade", "like", "%" . $userGrade . "%")->where("location", "like", "%" . $location . "%")->where("userGender", "like", "%" . $userGender . "%")->groupBy("courseAssessmentLog.userID")->selectRaw("courseAssessmentLog.userID, max(score) as score")->get();
                    $averageSum = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->where("groupRoleID", "like", "%" . $groupRoleID . "%")->where("userGrade", "like", "%" . $userGrade . "%")->where("location", "like", "%" . $location . "%")->where("userGender", "like", "%" . $userGender . "%")->selectRaw("round(avg(score), 0) as average")->get();
                    $averageRange = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->where("groupRoleID", "like", "%" . $groupRoleID . "%")->where("userGrade", "like", "%" . $userGrade . "%")->where("location", "like", "%" . $location . "%")->where("userGender", "like", "%" . $userGender . "%")->selectRaw("concat( MIN(score) , '-', MAX(score)) as average_range")->get();
                } else {
                    $queryForCompleted = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("status", "=", "pass")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->groupBy("courseAssessmentLog.userID")->selectRaw("max(score) as complete")->get();

                    $averageSum = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->selectRaw("round(avg(score), 0) as average")->get();

                    $averageRange = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->selectRaw("concat( MIN(score) , '-', MAX(score)) as average_range")->get();
                }

                $complete = count($queryForCompleted);
                $incomplete = $totalEnrolled - $complete;
                $course->complete = $complete;
                $course->incomplete = $incomplete;
                $course->averageSum = $averageSum[0]->average ?: $course->averageSum = null;
                $course->averageRange = $averageRange[0]->average_range ?? $course->averageRange = null;
            }

            return response()->json(["success" => true, "message" => $queryForCourses]);
        }
    }

    public function candidateTable(Request $req)
    {

        $token = $req->token;

        //Get admin userID by default to populate the table
        $queryForUserID = DB::table("users")->where("token", "=", $token)->selectRaw("userID")->get();
        $getUserID = $queryForUserID[0]->userID;

        $userID = $req->userID ?? $getUserID;

        if (DB::table("users")->where("userID", "=", $userID)->exists()) {

            $query = DB::table("users")->join("groupRole", "users.groupRoleId", "=", "groupRole.groupRoleId")->where("users.userID", "=", $userID)->selectRaw("employeeID, concat(userFirstName,' ' ,userLastName) as usersName, userEmail, groupRole.roleName, userGrade, location, userGender")->get();

            $queryForCandidate = DB::table("courseEnrolment")->join("course", "course.courseID", "=", "courseEnrolment.courseID")->where("courseEnrolment.userID", "=", $userID)->selectRaw("courseName, courseEnrolment.courseID")->get();

            foreach ($queryForCandidate as $course) {

                $courseID = $course->courseID;
                $averageRange = DB::table("courseAssessmentLog")->join("users", "users.userID", "=", "courseAssessmentLog.userID")->selectRaw("concat(min(score),'-',max(score)) as averageRange")->get();

                $status = DB::table("courseAssessmentLog")->join("users", "users.userID", "=", "courseAssessmentLog.userID")->where("courseAssessmentLog.userID", "=", $userID)->where("courseAssessmentLog.courseID", "=", $courseID)->select("status")->get();

                $started = DB::table("courseTrackerLog")->join("module", "courseTrackerLog.moduleID", "=", "module.moduleID")->join("course", "course.courseID", "=", "module.courseID")
                    ->where("courseTrackerLog.userID", "=", $userID)->where("course.courseID", "=", $courseID)->selectRaw("distinct(courseTrackerLog.moduleID) as started")->get();

                $moduleCompleted = count($started);

                $getModuleCount = DB::table('module')->join("course", "course.courseID", "=", "module.courseID")->where("course.courseID", "=", $courseID)->select("moduleID", "moduleName", "course.courseID")->get();
                $moduleCount = count($getModuleCount);

                $moduleProgress = $moduleCompleted . "/" . $moduleCount;
                $course->averageRange = $averageRange[0]->averageRange ?? $course->averageRange = null;
                (count($status) > 0) ? $course->status = $status[0]->status : $course->status = 'pending';
                (count($started) > 0) ? $course->started = true : $course->started = false;
                $course->moduleProgress = $moduleProgress;

                $candidateScore = DB::table('courseAssessmentLog')->selectRaw("max(score) as candidateScore")->where("courseID", "=", $courseID)->where("userID", "=", $userID)->get();
                $averageScore = DB::table("courseAssessmentLog")->selectRaw("ROUND(avg(score)) as nationalAverage")->where("courseID", "=", $courseID)->get();
                $course->candidateSummary = [$candidateScore[0], $averageScore[0]];
            }
            return response()->json(["success" => true, "candidateDetails" => $query, "candidateSummary" => $queryForCandidate]);
        } else {
            return response()->json(["success" => false, "message" => "User details incorrect"], 400);
        }
    }

    public function searchCandidate(Request $req)
    {
        $token = $req->token;
        $queryForCompanyId = DB::table("users")->where("token", "=", $token)->select("companyID")->get();
        $companyID = $queryForCompanyId[0]->companyID;

        if ($req->searchRequest) {
            $searchRequestVal = $req->searchRequest;

            if (strpos($searchRequestVal, ' ') !== false) {
                var_dump($searchRequestVal);
                $searchRequest = explode(" ", $searchRequestVal);
                $userFirstName = $searchRequest[0];
                $userLastName = $searchRequest[1];

                $searchQuery = DB::table("users")->where("companyID", "=", $companyID)->where("userFirstName", "like", "%" . $userFirstName . "%")->orWhere("userLastName", "like", "%" . $userLastName . "%")->selectRaw("concat(userFirstName,' ' ,userLastName) as usersName, userID")->get();
            } else {
                $searchQuery = DB::table("users")->where("companyID", "=", $companyID)->where(function ($query) use ($searchRequestVal) {
                    $query->where("userFirstName", "like", "%" . $searchRequestVal . "%")
                        ->orWhere("userLastName", "like", "%" . $searchRequestVal . "%")
                        ->orWhere("userEmail", "like", "%" . $searchRequestVal . "%")
                        ->orWhere("employeeID", "like", "%" . $searchRequestVal . "%");
                })->selectRaw("concat(userFirstName,' ' ,userLastName) as usersName, userID")->get();
            }

            return response()->json(["success" => true, "search" => $searchQuery]);
        } else {
            return response()->json(["success" => false, "search" => []]);
        }
    }

    public function courseView(Request $req)
    {
        $token = $req->token;
        $courseID = $req->courseID;
        $users = DB::table("users")->where("token", "=", $token)->get();
        $companyID = $users[0]->companyID;

        if (DB::table("course")->where("courseID", "=", $courseID)->exists()) {
            $enrolled = DB::table("courseEnrolment")->where("courseEnrolment.courseID", "=", $courseID)->where("companyID", "=", $companyID)->count();
            if ($enrolled > 0) {

                $completed = DB::table('courseAssessmentLog')->selectRaw("courseAssessmentLog.*, companyID")->join("users", "courseAssessmentLog.userID", "=", "users.userID")
                ->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->count();
                $notCompleted = $enrolled - $completed;
                $passed = DB::table('courseAssessmentLog')->select(["courseAssessmentLog.*", "companyID"])->join("users", "courseAssessmentLog.userID", "=", "users.userID")
                ->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->where("status", "=", "pass")->count();
                $failed = DB::table('courseAssessmentLog')->select(["courseAssessmentLog.*", "companyID"])->join("users", "courseAssessmentLog.userID", "=", "users.userID")
                ->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->where("status", "=", "fail")->count();
                
                $modules = DB::table("module")->where("courseID", "=", $courseID)->get();
                
                $courseUserDetails = array("noOfModules"=>count($modules), "enrolled" => $enrolled, "completed" => $completed, "notCompleted" => $notCompleted, "passed" => $passed, "failed"=>$failed);

                $moduleCompleted = [];
                foreach ($modules as $module) {
                    $moduleID = $module->moduleID;
                    $getModuleCompletedCount = DB::table('courseTrackerLog')->selectRaw("distinct(userID), courseTrackerLog.moduleID, status, courseID, moduleName, courseTrackerLog.created_at")
                    ->join("module", "courseTrackerLog.moduleID", "=", "module.moduleID")
                    ->where("courseID", "=", $courseID)->where("courseTrackerLog.moduleID", "=", $moduleID)->count();
                    array_push($moduleCompleted, array("module".$moduleID."Total" => $getModuleCompletedCount));
                }

                $page_number = $req->page_number ?? 1;
                $page_size = $req->page_size ?? 10;
                $offset = ($page_number - 1) * $page_size;
                

                $users = DB::table("courseEnrolment")->join("users", "courseEnrolment.userID", "=", "users.userID")->where("courseID", "=", $courseID)->where("courseEnrolment.companyID", "=", $companyID)->selectRaw("employeeID, concat(userFirstName,' ' ,userLastName) as usersName, users.userID")->skip($offset)->take($page_size)->get();

                $totalUsers = DB::table("courseEnrolment")->join("users", "courseEnrolment.userID", "=", "users.userID")->where("courseID", "=", $courseID)->where("courseEnrolment.companyID", "=", $companyID)->selectRaw("employeeID, concat(userFirstName,' ' ,userLastName) as usersName, users.userID")->count();

                foreach ($users as $user) {
                    $userID = $user->userID;
                    $queryForCompleted = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("courseAssessmentLog.userID", "=", $userID)->where("courseID", "=", $courseID)->where("users.companyID", "=", $companyID)->selectRaw("max(score) as score, status")->groupBy("courseAssessmentLog.Id")->get();

                    $getCompleted = DB::table("courseTrackerLog")->join("module", "courseTrackerLog.moduleID", "=", "module.moduleID")->join("course", "course.courseID", "=", "module.courseID")->join("users", "users.userID", "=", "courseTrackerLog.userID")->where("course.courseID", "=", $courseID)->where("users.companyID", "=", $companyID)->where("courseTrackerLog.userID", "=", $userID)->selectRaw("distinct(courseTrackerLog.userID) as userID, courseTrackerLog.moduleID, status, location, courseName, course.courseID, courseTrackerLog.created_at")->count();

                    $moduleProgress = $getCompleted . "/" . count($modules);
                    $user->moduleProgress=$moduleProgress;
                    $user->score = $queryForCompleted[0]->score  ?? $user->score = 0;
                    $user->status = $queryForCompleted[0]->status  ?? $user->status = 'pending';
                }
                // $averageSum = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->where("groupRoleID", "like", "%" . $groupRoleID . "%")->where("userGrade", "like", "%" . $userGrade . "%")->where("location", "like", "%" . $location . "%")->where("userGender", "like", "%" . $userGender . "%")->selectRaw("round(avg(score), 0) as average")->get();
                
                // $averageRange = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->where("groupRoleID", "like", "%" . $groupRoleID . "%")->where("userGrade", "like", "%" . $userGrade . "%")->where("location", "like", "%" . $location . "%")->where("userGender", "like", "%" . $userGender . "%")->selectRaw("concat( MIN(score) , '-', MAX(score)) as average_range")->get();



                // foreach ($courseCandidates as $course) {


                //     $completed = DB::table("courseTrackerLog")->selectRaw("count(status) as completed")->where("status", "=", "complete")->get();

                //     $not_completed = DB::table("courseTrackerLog")->selectRaw("count(status) as not_completed")->where("status", "=", "not complete")->get();

                //     $passed = DB::table("courseAssessmentLog")->selectRaw("count(status) as passed")->where("status", "=", "pass")->get();

                //     $failed = DB::table("courseAssessmentLog")->selectRaw("count(status) as failed")->where("status", "=", "fail")->get();

                //     $nationalAverage = DB::table("courseAssessmentLog")->selectRaw("ROUND(avg(score)) as nationalAverage")->get();

                //     $course->enrolled = $enrolled[0]->enrolled ?? $course->enrolled = null;
                //     $course->completed = $completed[0]->completed ?? $course->completed = null;
                //     $course->not_completed = $not_completed[0]->not_completed ?? $course->not_completed = null;
                //     $course->passed = $passed[0]->passed;
                //     $course->failed = $failed[0]->failed;
                //     $course->nationalAverage = $nationalAverage[0]->nationalAverage;

                //     // Course Engagement Chart
                //     $moduleCompletion = DB::table("module")->join("courseTrackerLog", "courseTrackerLog.moduleID", "=", "module.moduleID")->selectRaw("distinct(module.moduleID) as Modules, courseTrackerLog.status as Module_Completion")->where("status", "=", "complete")->get();

                //     $courseCompleted = DB::table("courseTrackerLog")->selectRaw("count(status) as completed")->where("status", "=", "complete")->get();

                //     $courseNotCompleted = DB::table("courseTrackerLog")->selectRaw("count(status) as not_completed")->where("status", "=", "not complete")->get();

                //     // $total=$courseCompleted + $courseNotCompleted;
                //     $course->courseEngagementChart = [$moduleCompletion[0], $courseCompleted[0], $courseNotCompleted[0]];
                // }

                //Course Table
                // $courseTable = DB::table("users")->join("courseTrackerLog", "courseTrackerLog.userID", "=", "users.userID")->join("module", "module.moduleID", "=", "courseTrackerLog.moduleID")->where("status", "=", "complete")->where("courseID", "=", $courseID)->selectRaw("users.employeeID,concat(userFirstName,' ' ,userLastName) as Name,users.userID,module.moduleName,module.courseID,module.moduleID,courseTrackerLog.status")->get();



                // foreach ($courseTable as $course) {

                //     $scoreRange = DB::table("courseAssessmentLog")->join("users", "users.userID", "=", "courseAssessmentLog.userID")->selectRaw("concat(min(score),'-',max(score)) as scoreRange")->where("courseID", "=", $courseID)->get();

                //     $started = DB::table("courseTrackerLog")
                //         ->join("module", "courseTrackerLog.moduleID", "=", "module.moduleID")
                //         ->join("course", "course.courseID", "=", "module.courseID")
                //         // ->where("courseTrackerLog.userID","=",$userID)
                //         ->selectRaw("distinct(courseTrackerLog.moduleID) as started")->get();

                //     $status = DB::table("courseAssessmentLog")->join("users", "users.userID", "=", "courseAssessmentLog.userID")->select("status")->get();

                //     $moduleCompleted = count($started);

                //     $getModuleCount = DB::table('module')->join("course", "course.courseID", "=", "module.courseID")->select("moduleID", "moduleName", "course.courseID")->get();
                //     $moduleCount = count($getModuleCount);

                //     $moduleProgress = $moduleCompleted . "/" . $moduleCount;
                //     $course->moduleProgress = $moduleProgress;
                //     $course->scoreRange = $scoreRange[0]->scoreRange ?? $course->scoreRange = null;
                //     $course->status = $status[0]->status ?? $course->status = null;
                // }
                return response()->json(["success" => true, "courseDetails" => $courseUserDetails, "courseEngagementChart" => $moduleCompleted, "totalUsers" => $totalUsers,  "courseTableDetails" => $users]);
            }else 
                return response()->json(["success" => false, "message" => "Course not assigned to company"], 401);
        } else 
            return response()->json(["success" => false, "message" => "Invalid Course ID"], 400);
        
    }
}
