<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientRegistrationController;
use App\Http\Controllers\EmployerRegistrationController;

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
    Route::post("/client/register", [EmployerRegistrationController::class, "Register"]);
    Route::post("/client/validate-otp", [EmployerRegistrationController::class, "OtpValidator"]);
    Route::post("/client/get/{client_id}", [EmployerRegistrationController::class, "getSingleClient"]);
    Route::post("/client/get", [EmployerRegistrationController::class, "getAllClient"]);
    Route::post("/client/delete/{client_id}", [EmployerRegistrationController::class, "deleteSingleClient"]);

    Route::post("/employer/register", [ClientRegistrationController::class, "Register"]);
    Route::post("/employer/validate-otp", [ClientRegistrationController::class, "OtpValidator"]);
    Route::post("/employer/get/{client_id}", [ClientRegistrationController::class, "getSingleClient"]);
    Route::post("/employer/get", [ClientRegistrationController::class, "getAllClient"]);
    Route::post("/employer/delete/{client_id}", [ClientRegistrationController::class, "deleteSingleClient"]);
});
