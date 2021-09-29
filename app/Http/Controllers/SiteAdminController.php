<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use VIPSoft\Unzip\Unzip;

class SiteAdminController extends Controller
{

    // Gets the base url by exploding the laravel "url" output


    private function getbaseUrl()
    {
        $explodedurl = explode("/", url("/"));
        return "https://" . $explodedurl[2];
    }

    public function getCompanies()
    {

        $companies =  DB::table("company")->join("users", "users.companyID", "=", "company.companyID")->where("users.userRoleID", "=", 1)->select(["company.companyID", "company.companyName as company_name", "company.companyAddress1 as company_address", "company.emailSuffix as company_email_suffix",  "company.companyAdminID", "users.userFirstName as admin_firstname", "users.userLastName as admin_lastname", "users.userEmail as admin_email", "company.companyCreateDate as create_at"])->get();

        foreach ($companies as $company) {
            $companyUsersNo = DB::table("users")->where("companyID", "=", $company->companyID)->count();
            $companyCourses = DB::table("courseEnrolment")->join("course", "course.courseID", "=", "courseEnrolment.courseID")->where("userID", "=", $company->companyAdminID)->select(["course.courseName", "courseEnrolment.enrolDate as purchased_at"])->get();
            $company->users_count = $companyUsersNo;
            $company->courses_list = $companyCourses;
        }

        return response()->json(["success" => true, "registeredCompanies" => $companies]);
    }

    public function getUsers()
    {
        $users = DB::table("users")->join("company", "company.companyID", "=", "users.companyID")->join("role", "role.roleID", "=", "users.userRoleID")->select(["users.userFirstName", "users.userLastName", "users.userEmail", "company.companyName", "role.roleName", "users.createdDate"])->get();
        return response()->json(["success" => true, "registeredUsers" => $users]);
    }

    public function createCourse(Request $req)
    {
        $courseName = $req->courseName;
        $courseDescription = $req->courseDescription;
        $courseCategory = $req->courseCategory;
        $coursePrice = $req->coursePrice;

        // Validate that a file was uploaded
        $validator = Validator::make($req->file(), [
            "courseImage" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => "Course Image not uploaded"], 400);
        }

        $courseImageName = $req->file("courseImage")->getClientOriginalName();
        $courseImagePath = $req->file("courseImage")->storeAs("CourseCoverImages", $courseImageName, "learningPlatformFolder");

        // Checks if courseName already exists
        if (DB::table("course")->where("courseName", "=", $courseName)->doesntExist()) {
            // Checks if file upload was successful
            if (!$courseImagePath) {
                return response()->json(["success" => false, "message" => "File not Uploaded"], 400);
            } else {
                $imagePath = $this->getbaseUrl() . "/" . $courseImagePath;

                DB::table("course")->insert(["courseName" => $courseName, "courseDescription" => $courseDescription, "price" => $coursePrice, "courseCategory" => $courseCategory, "image" => $imagePath]);

                return response()->json(["success" => true, "message" => "Course Creation Successful"]);
            }
        } else {
            return response()->json(["success" => false, "message" => "Course Already Exists"], 400);
        }
    }

    public function addModule(Request $req)
    {
        $moduleName = $req->moduleName;
        $moduleDescription = $req->moduleDescription;
        $courseID = $req->courseID;

        // Checks if course exists
        if (DB::table("course")->where("courseID", "=", $courseID)->exists()) {
            // Checks if module already exists
            if (DB::table("module")->where("courseID", "=", $courseID)->where("moduleName", "=", $moduleName)->doesntExist()) {
                $moduleID = DB::table("module")->insertGetId(["moduleName" => $moduleName, "moduleDescription" => $moduleDescription, "courseID" => $courseID]);
                return response()->json(["success" => true, "message" => "Module Added", "moduleID" => $moduleID]);
            } else {
                return response()->json(["success" => true, "message" => "Module already exist"], 400);
            }
        } else {
            return response()->json(["success" => false, "message" => "Course does not exist"], 400);
        }
    }

    public function addTopic(Request $req)
    {
        $topicName = $req->topicName;
        $topicDuration = $req->topicDuration;
        $moduleID = $req->moduleID;
        $courseID = $req->courseID;

        // Validate that a file was uploaded
        $validator = Validator::make($req->file(), [
            "folderzip" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => "Topic Folder not uploaded"], 400);
        }
        $topicFolderName = $req->file("folderzip")->getClientOriginalName();
        $topicFolderPath = $req->file("folderzip")->storeAs("TopicFolders", $topicFolderName, "learningPlatformFolder");

        // Check if module ID exists for that paticular course
        if (DB::table("module")->where("moduleID", "=", $moduleID)->where("courseID", "=", $courseID)->exists()) {
            // Checks if the topic has already been added
            if (DB::table("topic")->where("topicName", "=", $topicName)->where("moduleID", "=", $moduleID)->doesntExist()) {
                // Check of folder was uploaded successfully
                if (!$topicFolderPath) {
                    return response()->json(["success" => false, "message" => "Folder not Uploaded"], 400);
                } else {
                    $foldername = explode(".", $topicFolderName)[0];
                    $folderPath = $this->getbaseUrl() . "/" . "TopicFolders" . "/" . $foldername;

                    $unzipper = new Unzip();
                    // Unzip the zip folder uploaded above
                    $files = $unzipper->extract(storage_path("../../") . $topicFolderPath, storage_path("../../TopicFolders"));
                    // Check if Zip File still exists then delete
                    if (File::exists(storage_path("../../") . $topicFolderPath)) {
                        File::delete(storage_path("../../") . $topicFolderPath);
                    }

                    DB::table("topic")->insert(["topicName" => $topicName, "moduleID" => $moduleID, "duration" => $topicDuration, "folder" => $folderPath]);

                    return response()->json(["success" => true, "message" => "Topic Added"]);
                }
            } else {
                return response()->json(["success" => false, "message" => "Topic already exists"], 400);
            }
        } else {
            return response()->json(["success" => false, "message" => "Module does not exist"], 400);
        }
    }

    public function testFileUpload(Request $req)
    {
        $name = $req->file("image")->getClientOriginalName();

        // $path = $req->file("image")->store("images");

        $path = $this->getbaseUrl() . "/" . $req->file("image")->storeAs("CourseCoverImages", $name, "learningPlatformFolder");;

        // $path = $req->file("image")->storeAs("../../../../CourseCoverImages", $name);

        if (!$path) {
            return response()->json(["success" => false, "message" => "File not Uploaded"], 400);
        } else {
            // return response()->json(["success" => true, "message" => "Upload Sucessful", "path" => url("/") . "/" . $path]);
            // echo $_SERVER;
            return response()->json(["success" => true, "message" => "Upload Sucessful", "path" => $path,]);
        }
    }

    public function testFolderUpload(Request $req)
    {
        $name = $req->file("folderzip")->getClientOriginalName();

        $foldername = explode(".", $name)[0];


        // $path = $this->getbaseUrl() . "/" . $req->file("folderzip")->storeAs("CourseCoverImages", $name, "learningPlatformFolder");;

        $path = $req->file("folderzip")->storeAs("TopicFolders", $name, "learningPlatformFolder");

        if (!$path) {


            return response()->json(["success" => false, "message" => "File not Uploaded"], 400);
        } else {

            $unzipper = new Unzip();
            // Unzip the zip folder uploaded above
            $files = $unzipper->extract(storage_path("../../") . $path, storage_path("../../TopicFolders"));
            // Check if Zip File still exists then delete
            if (File::exists(storage_path("../../") . $path)) {
                File::delete(storage_path("../../") . $path);
            }
            return response()->json(["success" => true, "message" => "Upload Sucessful", "foldername" => $foldername, "name" => $name]);
        }
    }
}
