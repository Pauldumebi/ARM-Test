<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteAdminController extends Controller
{

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
}
