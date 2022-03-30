<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
 
    public function token($length)
    {
        $code = "";
        $total = 0;
        do {
            if (rand(0, 1) == 0) {
                $code .= chr(rand(97, 122)); // ASCII code from **a(97)** to **z(122)**
            } else {
                $code .= rand(0, 6); // Numbers!!
            }
            $total++;
        } while ($total < (int)$length);
        return $code;
    }

    public function generateCode ($digits) {
        return rand(pow(10, $digits-1), pow(10, $digits)-1);
    }

    public function formatIntlPhoneNo($phone)
    {
        if (substr($phone, 0, 1) === '0') {
            return '234' . substr($phone, 1);
        }
        return $phone;
    }
}