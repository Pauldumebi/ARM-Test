<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteAdminController;
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

// Auth Controller Endpoints
Route::prefix("v1")->group(function () {
    Route::post("/signup", [AuthController::class, "signup"]);
    Route::post("/login", [AuthController::class, "login"]);
    Route::get("/verifyemail/{token}", [AuthController::class, "verifyEmail"]);
    Route::post("/forgot-password", [AuthController::class, "forgotPassword"]);
    Route::post("/update-forgot-password", [AuthController::class, "updateForgotPassword"]);
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
    Route::get("/groups-company/{token}", [GroupController::class, "fetchCompanyGroup"]);
    Route::post("/group-course", [GroupController::class, "assignCourse"]);
    Route::delete("/group-course", [GroupController::class, "unassignCourse"]);
    Route::post("/courses-group", [GroupController::class, "fetchGroupCourse"]);
    Route::post("/group-user", [GroupController::class, "addUser"]);
    Route::delete("/group-user", [GroupController::class, "removeUser"]);
    Route::post("/users-group", [GroupController::class, "fetchGroupUser"]);
});

// Profile Page Controller Endpoints
Route::prefix("v1")->group(function () {
    Route::get("/profile/{token}", [ProfileController::class, "getUserDetails"]);
    Route::post("/profile/{token}", [ProfileController::class, "updateUserDetails"]);
    Route::post("/company-profile/{token}", [ProfileController::class, "updateCompanyDetails"]);
    Route::post("/password/{token}", [ProfileController::class, "updatePassword"]);
});

// Order Page Controller Endpoints
Route::prefix("v1")->group(function () {
    Route::get("/orders/{token}", [OrderController::class, "getOrders"]);
    Route::post("/orders", [OrderController::class, "checkoutOrders"]);
});

// Site Admin Page Controller Endpoints
Route::prefix("v1")->group(function () {
    Route::get("/registered-companies", [SiteAdminController::class, "getCompanies"]);
    Route::get("/registered-users", [SiteAdminController::class, "getUsers"]);
    Route::post("/admin-course", [SiteAdminController::class, "createCourse"]);
    Route::post("/admin-module", [SiteAdminController::class, "addModule"]);
    Route::post("/admin-topic", [SiteAdminController::class, "addTopic"]);
    Route::post("/test-upload", [SiteAdminController::class, "testFileUpload"]);
    Route::post("/test-folderupload", [SiteAdminController::class, "testFolderUpload"]);
});
