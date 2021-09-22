<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('send-mail', function () {
   
//     $details = [
//         'name' => '',
//         'email' => '',
//         'link' => '',
//         'websiteLink' => ''
//     ];
   
//     \Mail::to('your_receiver_email@gmail.com')->send(new \App\Mail\VerifyEmail($details));
//     return response()->json(["success" => true, "message" => 'Email is Sent'], 200);
//     dd("Email is Sent.");
// });