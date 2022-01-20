<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    public function filterParams (Request $req) {
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

    public function allCourses (Request $req) {
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
                $queryForCourses = DB::table("courseEnrolment")->join("course", "courseEnrolment.CourseID", "=", "course.CourseID")->join("users", "courseEnrolment.userID", "=", "users.userID")->where("courseEnrolment.companyID", "=", $companyID)->where("groupRoleID", "like", "%". $groupRoleID."%")->where("userGrade", "like", "%". $userGrade."%")->where("location", "like", "%". $location."%")->where("userGender", "like", "%". $userGender."%")->selectRaw("any_value(courseEnrolment.companyID) as companyID, course.courseID, courseName, count(courseEnrolment.userID) as enrolled")->groupBy("courseID")->get();
            } else
                $queryForCourses = DB::table("courseEnrolment")->join("course", "courseEnrolment.CourseID", "=", "course.CourseID")->where("companyID", "=", $companyID)->selectRaw("any_value(companyID) as companyID, course.courseID, courseName, count(userID) as enrolled")->groupBy("courseID")->get();

            if (!$queryForCourses) {
                $queryForCourses = [];
                return response()->json(["success" => true, "message" => $queryForCourses]);
            }

            foreach ($queryForCourses as $course) {
                $courseID=$course->courseID;
                $totalEnrolled =$course->enrolled;

                if ($userGender || $location || $userGrade || $roleName) {
                    $queryForCompleted = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("status", "=", "pass")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->where("groupRoleID", "like", "%". $groupRoleID."%")->where("userGrade", "like", "%". $userGrade."%")->where("location", "like", "%". $location."%")->where("userGender", "like", "%". $userGender."%")->groupBy("courseAssessmentLog.userID")->selectRaw("courseAssessmentLog.userID, max(score) as score")->get();
            
                    $averageSum = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->where("groupRoleID", "like", "%". $groupRoleID."%")->where("userGrade", "like", "%". $userGrade."%")->where("location", "like", "%". $location."%")->where("userGender", "like", "%". $userGender."%")->selectRaw("round(avg(score), 0) as average")->get();

                    $averageRange = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->where("groupRoleID", "like", "%". $groupRoleID."%")->where("userGrade", "like", "%". $userGrade."%")->where("location", "like", "%". $location."%")->where("userGender", "like", "%". $userGender."%")->selectRaw("concat( MIN(score) , '-', MAX(score)) as average_range")->get();

                } else {
                    $queryForCompleted = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("status", "=", "pass")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->groupBy("courseAssessmentLog.userID")->selectRaw("max(score) as complete")->get();
            
                    $averageSum = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->selectRaw("round(avg(score), 0) as average")->get();

                    $averageRange = DB::table("courseAssessmentLog")->join("users", "courseAssessmentLog.userID", "=", "users.userID")->where("courseID", "=", $courseID)->where("companyID", "=", $companyID)->selectRaw("concat( MIN(score) , '-', MAX(score)) as average_range")->get();
                }
                
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

    // public function candidateDetails (Request $req ){
    //     $token=$req->token;
    //     $email= $req->userEmail;
    //     if (DB::table("users")->where("userID","=",$userID)->exists()) {
    //         // $queryUserTable = DB::table("users")->where("token", "=", $token)->orWhere("email", "=", $email)->get();
        
    //         // if (count($queryUserTable) === 1) {
    //          $query = DB::table("users")->join("role", "users.userRoleID", "=", "role.RoleID")->where("token", "=", $token)->select(["users.userID", "users.userFirstName", "users.userEmail","role.roleName","users.userGrade","users.location","users.userGender"])->get();

    //          return response()->json(["success" => true, "message" => $query]);
    //         }     
    //     else{
    //             return response()->json(["success" => false, "message" => "User details incorrect"]);
    //         }
        
    // }

    public function candidateTable (Request $req){

        $token=$req->token;
        $userID=$req->userID;
       
        if (DB::table("users")->where("userID","=",$userID)->exists()) {
            //  var_dump($userID);

             $query = DB::table("users")->join("role", "users.userRoleID", "=", "role.RoleID")->join("courseEnrolment","courseEnrolment.userID","=","users.userID")->where("users.userID", "=", $userID)->selectRaw("count(distinct(courseID)) as totalCourses,users.userID,users.employeeID, concat(userFirstName,' ' ,userLastName) as usersName, users.userEmail,role.roleName,users.userGrade,users.location,users.userGender")->groupBy("users.userID")->get();

            // var_dump($query);
           
       
            $queryForCandidate=DB::table("courseAssessmentLog")->join("course","course.courseID","=","courseAssessmentLog.courseID")->where("courseAssessmentLog.userID","=",$userID)->selectRaw("courseName,max(score) as score,any_value(courseAssessmentLog.courseID) as courseID")->groupBy("courseName")->get();

            foreach($queryForCandidate as $course){
            
                $courseID=$course->courseID;
            
                // var_dump($courseID);

                $averageRange=DB::table("courseAssessmentLog")->join("users","users.userID","=","courseAssessmentLog.userID")->selectRaw("concat(min(score),'-',max(score)) as averageRange")->get();

                $status=DB::table("courseAssessmentLog")->join("users","users.userID","=","courseAssessmentLog.userID")->select("status")->get();

                $started=DB::table("courseTrackerLog")
                ->join("module","courseTrackerLog.moduleID","=","module.moduleID")
                ->join("course","course.courseID","=","module.courseID")
                ->where("courseTrackerLog.userID","=",$userID)
                ->where("course.courseID","=",$courseID)->selectRaw("distinct(courseTrackerLog.moduleID) as started")->get();
                
                $moduleCompleted=count($started);

                $getModuleCount=DB::table('module')->join("course","course.courseID","=","module.courseID")->where("course.courseID","=",$courseID)->select("moduleID","moduleName","course.courseID")->get();
                $moduleCount=count($getModuleCount);

                $moduleProgress=$moduleCompleted."/".$moduleCount;
        

                $course->averageRange=$averageRange[0]->averageRange ?? $course->averageRange=null;
                
                $course->status=$status[0]->status ?? $course->status=null;
                $course->started=true?? $course->started=null;
                $course->moduleProgress=$moduleProgress;

                //Candidate score summary

                // $scoreSummary=DB::select("SELECT TRIM(max(score))+0 as score_summary from courseAssessmentLog
                // where userID=$userID and courseID=$courseID 
                // UNION  SELECT avg(score)  FROM courseAssessmentLog where courseID=$courseID");

                $candidateScore=DB::table('courseAssessmentLog')->selectRaw("max(score) as candidateScore")->where("courseID","=",$courseID)->where("userID","=",$userID)->get();
                
                $averageScore=DB::table("courseAssessmentLog")->selectRaw("ROUND(avg(score)) as nationalAverage")->where("courseID","=",$courseID)->get();
                
                $course->candidateSummary=[$candidateScore,$averageScore];
            }
            return response()->json(["success" => true, "candidateDetails" =>$query, "candidateSummary" => $queryForCandidate]);
            
            

        } else{
            return response()->json(["success" => false, "message" => "User details incorrect"]);
        }
    }
    public function searchCandidate (Request $req){

        if ($req->searchRequest){
            $searchRequest = $req->searchRequest;

            if (strpos($searchRequest, ' ') !== false) {
                $searchRequest=explode(" ", $searchRequest);
                $userFirstName=$searchRequest[0];
                $userLastName=$searchRequest[1];
            }else{
                $userFirstName=$searchRequest;
                $userLastName=null;
            }

            $searchQuery = DB::table("users")->where("userFirstName", "like", "%". $userFirstName."%")->where("userLastName", "like", "%". $userLastName."%")->selectRaw("concat(userFirstName,' ' ,userLastName) as usersName, userID")->get();

         

          return response()->json(["success" => true, "search"=> $searchQuery]);
        }
        else{
            return response()->json(["success" => false, "search"=> []]);
        }
       
    }
    public function courseView(Request $req){
        $token=$req->token;
        $courseID=$req->courseID;
        if (DB::table("course")->where("courseID","=",$courseID)->exists()) {
            $courseCandidates=DB::table('module')->selectRaw("count(distinct(moduleID)) as module")->get();
            foreach($courseCandidates as $course){
                //  $courseID=$course->courseID;

                 $enrolled=DB::table("courseEnrolment")->selectRaw("count(distinct(courseEnrolmentID)) as enrolled")->get();

                 $completed=DB::table("courseTrackerLog")->selectRaw("count(status) as completed")->where("status","=","complete")->get();

                 $not_completed=DB::table("courseTrackerLog")->selectRaw("count(status) as not_completed")->where("status","=","not complete")->get();

                 $passed=DB::table("courseAssessmentLog")->selectRaw("count(status) as passed")->where("status","=","pass")->get();

                 $failed=DB::table("courseAssessmentLog")->selectRaw("count(status) as failed")->where("status","=","fail")->get();

                 $nationalAverage=DB::table("courseAssessmentLog")->selectRaw("ROUND(avg(score)) as nationalAverage")->get();

                $course->enrolled=$enrolled[0]->enrolled ?? $course->enrolled=null;
                $course->completed=$completed[0]->completed ?? $course->completed=null;
                $course->not_completed=$not_completed[0]->not_completed ?? $course->not_completed=null;
                $course->passed=$passed[0]->passed;
                $course->failed=$failed[0]->failed;
                $course->nationalAverage=$nationalAverage[0]->nationalAverage;

                // Course Engagement Chart
                $moduleCompletion=DB::table("module")->join("courseTrackerLog","courseTrackerLog.moduleID", "=", "module.moduleID")->selectRaw("distinct(module.moduleID) as Modules, courseTrackerLog.status as Module_Completion")->where("status","=","complete")->get();
                
                $courseCompleted=DB::table("courseTrackerLog")->selectRaw("count(status) as completed")->where("status","=","complete")->get();

                $courseNotCompleted=DB::table("courseTrackerLog")->selectRaw("count(status) as not_completed")->where("status","=","not complete")->get();

                // $total=$courseCompleted + $courseNotCompleted;
                $course->courseEngagementChart=[$moduleCompletion[0],$courseCompleted[0],$courseNotCompleted[0]];
            }

             //Course Table
             $courseTable= DB::table("users")->join("courseTrackerLog","courseTrackerLog.userID","=","users.userID")->join("module","module.moduleID","=", "courseTrackerLog.moduleID")->where("status","=","complete")->where("courseID","=",$courseID)->selectRaw("users.employeeID,concat(userFirstName,' ' ,userLastName) as Name,users.userID,module.moduleName,module.courseID,module.moduleID,courseTrackerLog.status")->get();

        

            foreach($courseTable as $course){

                $scoreRange=DB::table("courseAssessmentLog")->join("users","users.userID","=","courseAssessmentLog.userID")->selectRaw("concat(min(score),'-',max(score)) as scoreRange")->where("courseID","=",$courseID)->get();

                $started=DB::table("courseTrackerLog")
                ->join("module","courseTrackerLog.moduleID","=","module.moduleID")
                ->join("course","course.courseID","=","module.courseID")
                // ->where("courseTrackerLog.userID","=",$userID)
                ->selectRaw("distinct(courseTrackerLog.moduleID) as started")->get();

                $status=DB::table("courseAssessmentLog")->join("users","users.userID","=","courseAssessmentLog.userID")->select("status")->get();
                
                $moduleCompleted=count($started);

                $getModuleCount=DB::table('module')->join("course","course.courseID","=","module.courseID")->select("moduleID","moduleName","course.courseID")->get();
                $moduleCount=count($getModuleCount);

                $moduleProgress=$moduleCompleted."/".$moduleCount;
                $course->moduleProgress=$moduleProgress;
                $course->scoreRange=$scoreRange[0]->scoreRange ?? $course->scoreRange=null;
                $course->status=$status[0]->status ?? $course->status=null;

            }
            return response()->json(["success" => true, "courseDetails"=>$courseCandidates, "courseEngagementChart" =>$courseCandidates,"courseTableDetails"=>$courseTable]);
        }
        else{
            return response()->json(["success" => false, "message"=>"Invalid Course ID"], 400);
        }
    }
}