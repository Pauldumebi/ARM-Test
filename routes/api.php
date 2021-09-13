<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::get("/test", function (Request $request) {
//     return "Flex";
//     //for reasons i don't know you have sent "Accept: application/json" on POSTMAN to get a good response
// });

// Auth Controller Endpoints
Route::prefix("v1")->group(function () {
    Route::post("/signup", [AuthController::class, "signup"]);
    Route::post("/login", [AuthController::class, "login"]);
});

// User Controller Endpoints
Route::prefix("v1")->group(function () {
    Route::post("/user", [UserController::class, "createCompanyUser"]);
    Route::post("/companyusers", [UserController::class, "getCompanyUsers"]);
});

// Course Controller Endpoints
Route::prefix("v1")->group(function () {
    Route::get("/course", [CoursesController::class, "getCourses"]);
    Route::post("/course-enrollment", [CoursesController::class, "enrolToCourse"]);
    Route::post("/company-enrollment", [CoursesController::class, "enrolCompanyToCourse"]);
    Route::post("/enrolled-courses", [CoursesController::class, "getEnrolledCourses"]);
    Route::post("/modules-topics", [CoursesController::class, "getCourseModuleTopics"]);
    Route::post("/course-seats", [CoursesController::class, "getCourseSeats"]);
    Route::post("/assignment-courses", [CoursesController::class, "getCoursesAssignment"]);
});

// Group Controller Endpoints
Route::prefix("v1")->group(function () {
    Route::post("/group", [GroupController::class, "createGroup"]);
    Route::delete("/group", [GroupController::class, "removeGroup"]);
    Route::post("/groups-company", [GroupController::class, "fetchCompanyGroup"]);
    Route::post("/group-course", [GroupController::class, "assignCourse"]);
    Route::delete("/group-course", [GroupController::class, "unassignCourse"]);
    Route::post("/group-user", [GroupController::class, "addUser"]);
    Route::delete("/group-user", [GroupController::class, "removeUser"]);
    Route::post("/users-group", [GroupController::class, "fetchGroupUsers"]);
});
