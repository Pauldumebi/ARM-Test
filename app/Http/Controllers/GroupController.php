<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{

    private function isAdmin($token)
    {
        // Checks if token has admin priviledges and returns companyID of Admin
    }

    public function createGroup(Request $req)
    {
    }

    public function removeGroup(Request $req)
    {
    }

    public function fetchCompanyGroup(Request $req)
    {
    }

    public function assignCourse(Request $req)
    {
    }

    public function unassignCourse(Request $req)
    {
    }

    public function addUser(Request $req)
    {
    }

    public function removeUser(Request $req)
    {
    }

    public function fetchGroupUser(Request $req)
    {
    }
}
