<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteAdminController extends Controller
{

    public function getCompanies()
    {

        $companies =  DB::table("company")->join("users", "users.companyID", "=", "company.companyID")->where("users.userRoleID", "=", 1)->select(["company.companyID", "company.companyAddress1", "company.emailSuffix", "company.companyCreateDate", "company.companyAdminID", "users.userFirstName", "users.userLastName", "users.userEmail"])->get();

        foreach ($companies as $company) {
            $companyUsersNo = DB::table("users")->where("companyID", "=", $company->companyID)->count();
            $companyCoursesNo = DB::table("courseEnrolment")->where("userID", "=", $company->companyAdminID)->count();
            $company->usersNumber = $companyUsersNo;
            $company->coursesNumber = $companyCoursesNo;
        }
    }

    public function getUsers()
    {
    }
}
