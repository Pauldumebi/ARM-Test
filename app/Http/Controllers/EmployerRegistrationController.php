<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmployerRegistrationController extends Controller
{
    private function sendUserCreationEmail($first_name, $email, $otp)
    {
        $details = [
            'name' => $first_name,
            'otp' => $otp,
        ];
        Mail::to($email)->send(new \App\Mail\CreateUser($details));
    }

    public function Register(Request $request)
    {
        $surname = $request->surname;
        $first_name = $request->first_name;
        $email = $request->email;
        $mobile = $request->mobile;
        $address = $request->address;
        $state_of_location = $request->state_of_location;
        $employer_name = $request->employer_name;

        //Validation the Data
        $validator = Validator::make(
            [
                'surname' => 'required',
                'first_name' => 'required',
                'email' => 'required|email|unique:users',
                'mobile' => 'required',
                'address' => 'required',
                'state_of_location' => 'required',
                'employer_name' => 'required',
            ]
        );


        if ($validator->fails()) {
            $messages = $validator->failed();
            return response()->json(["false" => true, "message" => $messages]);
        }

        $status = "pending";
        $otp = $this->generateCode(5);
        $token = bcrypt($email);
        // $otp = new MSG91();

        DB::table("employer")->insert(["surname" => $surname, "first_name" => $first_name, "email" => $email,  "mobile" => $this->formatIntlPhoneNo($mobile), "address" => $address, "state_of_location" => $state_of_location, "employer_name" => $employer_name, "status" => $status, "otp" => $otp, "token" => $token]);

        $this->sendUserCreationEmail($first_name, $email, $otp);

        return response()->json(["success" => true, "message" => "successfully inserted"]);
    }

    public function OtpValidator(Request $request)
    {
        $otp =  $request->otp;
        $mobile =  $request->mobile;
        $email =  $request->email;

        // Checks if email or phone exist against a code
        $employer = DB::table("employer")
            ->where("otp", "=", $otp)->where(function ($query) use ($email, $mobile) {
                $query->where("email", "=", $email)
                    ->orWhere("mobile", "=", $mobile);
            })->get();


        if (count($employer) === 1) {
            $status = "complete";
            $pension_number = "EMP" . random_int(100000000000, 999999999999);

            DB::table("employer")->where("email", "=", $email)->orWhere("mobile", "=", $mobile)->update(["status" => $status, "pension_number" => $pension_number]);

            return response()->json(["success" => true, "message" => "User profile updated", "pension_number" => $pension_number]);
        } else {
            return response()->json(["success" => false, "message" => "email or phone number or code does not exists"], 400);
        }
    }

    public function getSingleClient(Request $request)
    {
        $employer_id =  $request->employer_id;
        $client = DB::table("employer")->where("employer_id", "=", $employer_id)->get();
        
        if (count($client) === 1) {
            return response()->json(["success" => true, "data" => $client]);
        } else {
            return response()->json(["success" => false, "message" => "does not exist"], 400);
        }
    }
    
    public function deleteSingleClient(Request $request)
    {
        if (DB::table("employer")->where("employer_id", "=", $request->employer_id)->exists()) {
            $client = DB::table("employer")->where("employer_id", "=", $request->client_id)->delete();
            return response()->json(["success" => true, "data" => $client]);
        } else {
            return response()->json(["success" => false, "message" => "does not exist"], 400);
        }
    }

    public function getAllClient()
    {
        $client = DB::table("employer")->get();
        
        if (count($client) === 1) {
            return response()->json(["success" => true, "data" => $client]);
        } else {
            return response()->json(["success" => false, "message" => "does not exist"], 400);
        }
    }
}