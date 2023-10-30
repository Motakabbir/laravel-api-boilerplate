<?php

namespace App\Http\Controllers\Common;
use Illuminate\Http\Request;

use Auth;

class SmsController
{
    /**
     * BASE64 TO IMAGE CONVERT FUNCTION
     */
    static public function sendTo($data){
        $url = env('SMS_URL');
        $data= array(
            'username' => env('SMS_USERMANE'),
            'password' => env('SMS_PASSWORD'),
            'number' => $data['number'],
            'message' => $data['msg']
        );

        // return $data;

        $ch = curl_init(); // Initialize cURL
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $smsresult = curl_exec($ch);
       // $p = explode("|",$smsresult);
       // return $sendstatus = $p[0];
       return  $sendstatus = 1;
    }

    static public function smsStatus($code='1000'){
        $arr = ['1000' => 'Invalid user or Password',
        '1002' => 'Empty Number',
        '1003' => 'Invalid message or empty message',
        '1004' => 'Invalid number',
        '1005' => 'All Number is Invalid',
        '1006' => 'insufficient Balance',
        '1009' => 'Inactive Account',
        '1010' => 'Max number limit exceeded',
        '1101' => 'Success'];

        return $arr[$code];
    }
}
?>