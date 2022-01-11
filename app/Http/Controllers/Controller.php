<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getUrl()
    {
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $parts = parse_url($actual_link);
        $scheme = explode('/', $parts['scheme']);
        $host = explode('/', $parts['host']);
        $hostUrl = $scheme[0] . "://" . $host[0];
        return $hostUrl;
    }
    private function OrderID($length = 6)
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
        } while ($total < $length);
        return $code;
    }

    public function formatIntlPhoneNo($phone)
    {
        if (substr($phone, 0, 1) === '0') {
            return '234' . substr($phone, 1);
        }
        return $phone;
    }

    public function RandomCode($length = 50)
    {
        $code = '';
        $total = 0;
        do {
            if (rand(0, 1) == 0) {
                $code .= chr(rand(97, 122)); // ASCII code from **a(97)** to **z(122)**
            } else {
                $code .= rand(0, 9); // Numbers!!
            }
            $total++;
        } while ($total < $length);
        return $code;
    }
}
