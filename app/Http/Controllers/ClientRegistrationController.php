<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ClientRegistrationController extends Controller
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
        $state_of_residence = $request->state_of_residence;
        $employer_code = $request->employer_code;
        $next_of_kin_surname = $request->next_of_kin_surname;
        $next_of_kin_first_name = $request->next_of_kin_first_name;
        $next_of_kin_mobile = $request->next_of_kin_mobile;
        $next_of_kin_email = $request->next_of_kin_email;

        //Validation the Data

        $validator = Validator::make(
            [
                'surname' => 'required',
                'first_name' => 'required',
                'email' => 'required|email|unique:users',
                'mobile' => 'required',
                'address' => 'required',
                'state_of_residence' => 'required',
                'employer_code' => 'required',
                'next_of_kin_surname' => 'required',
                'next_of_kin_first_name' => 'required',
                'next_of_kin_mobile' => 'required',
                'next_of_kin_email' => 'required|email|unique:users',
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

        DB::table("client")->insert(["surname" => $surname, "first_name" => $first_name, "email" => $email,  "mobile" => $this->formatIntlPhoneNo($mobile), "address" => $address, "state_of_residence" => $state_of_residence, "employer_code" => $employer_code, "status" => $status, "otp" => $otp, "next_of_kin_surname"=> $next_of_kin_surname,
        "next_of_kin_first_name"=> $next_of_kin_first_name, "next_of_kin_mobile" => $this->formatIntlPhoneNo($next_of_kin_mobile), "next_of_kin_email" => $next_of_kin_email, "token" => $token]);

        $this->sendUserCreationEmail($first_name, $email, $otp);


        return response()->json(["success" => true, "message" => "successfully inserted"]);
    }

    public function UpdateRecord(Request $request)
    {
        $surname = $request->surname;
        $first_name = $request->first_name;
        $email = $request->email;
        $mobile = $request->mobile;
        $address = $request->address;
        $state_of_residence = $request->state_of_residence;
        $employer_code = $request->employer_code;
        $next_of_kin_surname = $request->next_of_kin_surname;
        $next_of_kin_first_name = $request->next_of_kin_first_name;
        $next_of_kin_mobile = $request->next_of_kin_mobile;
        $next_of_kin_email = $request->next_of_kin_email;
        $token = $request->token;

        //Validation the Data

        $validator = Validator::make(
            [
                'surname' => 'required',
                'first_name' => 'required',
                'email' => 'required|email|unique:users',
                'mobile' => 'required',
                'address' => 'required',
                'state_of_residence' => 'required',
                'employer_code' => 'required',
                'next_of_kin_surname' => 'required',
                'next_of_kin_first_name' => 'required',
                'next_of_kin_mobile' => 'required',
                'next_of_kin_email' => 'required|email|unique:users',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->failed();
            return response()->json(["false" => true, "message" => $messages]);
        }

        DB::table("client")->where("token", "=", $token)->update(["surname" => $surname, "first_name" => $first_name, "email" => $email,  "mobile" => $this->formatIntlPhoneNo($mobile), "address" => $address, "state_of_residence" => $state_of_residence, "employer_code" => $employer_code, "next_of_kin_surname"=> $next_of_kin_surname,
        "next_of_kin_first_name"=> $next_of_kin_first_name, "next_of_kin_mobile" => $this->formatIntlPhoneNo($next_of_kin_mobile), "next_of_kin_email" => $next_of_kin_email]);

        return response()->json(["success" => true, "message" => "Record successfully updated"]);
    }

    public function OtpValidator(Request $request)
    {
        $otp =  $request->otp;
        $mobile =  $request->mobile;
        $email =  $request->email;

        // Checks if email or phone exist against a code
        $client = DB::table("client")
        ->where("otp", "=", $otp)->where(function ($query) use ($email, $mobile) {
            $query->where("email", "=", $email)
            ->orWhere("mobile", "=", $mobile);
        })->get();

        
        if (count($client) === 1) {
            $status = "complete";
            $pension_number = "PEN".random_int(100000000000, 999999999999);

            DB::table("client")->where("email", "=", $email)->orWhere("mobile", "=", $mobile)->update(["status" => $status, "pension_number" => $pension_number]);

            return response()->json(["success" => true, "message" => "User profile updated" , "pension_number" => $pension_number]);
        } else {
            return response()->json(["success" => false, "message" => "email or phone number or code does not exists"], 400);
        }
    }

    public function getSingleClient(Request $request)
    {
        $client_id =  $request->client_id;
        $client = DB::table("client")->where("client_id", "=", $client_id)->get();
        
        if (count($client) === 1) {
            return response()->json(["success" => true, "data" => $client]);
        } else {
            return response()->json(["success" => false, "message" => "does not exist"], 400);
        }
    }

    public function deleteSingleClient(Request $request)
    {
        if (DB::table("client")->where("client_id", "=", $request->client_id)->exists()) {
            $client = DB::table("client")->where("client_id", "=", $request->client_id)->delete();
            return response()->json(["success" => true, "data" => $client]);
        } else {
            return response()->json(["success" => false, "message" => "does not exist"], 400);
        }
    }

    public function getAllClient()
    {
        $client = DB::table("client")->get();
        
        if (count($client) === 1) {
            return response()->json(["success" => true, "data" => $client]);
        } else {
            return response()->json(["success" => false, "message" => "does not exist"], 400);
        }
    }
}
