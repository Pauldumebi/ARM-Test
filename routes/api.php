<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoursesController;
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

Route::prefix("v1")->group(function () {
    Route::post("/signup", [AuthController::class, "signup"]);
    Route::post("/login", [AuthController::class, "login"]);
    Route::post("/user", [UserController::class, "createCompanyUser"]);
    Route::post("/companyusers", [UserController::class, "getCompanyUsers"]);
    Route::get("/course", [CoursesController::class, "getCourses"]);
    Route::post("/course-enrollment", [CoursesController::class, "enrolToCourse"]);
    Route::post("/enrolled-courses", [CoursesController::class, "getEnrolledCourses"]);
    Route::post("/modules-topics", [CoursesController::class, "getCourseModuleTopics"]);
});
