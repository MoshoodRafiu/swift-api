<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SMSController extends Controller
{
    public static function sendPhoneVerificationSMS($user): array
    {
        return Http::post('https://termii.com/api/sms/otp/send', [
            "api_key" => env('TERMI_KEY'),
            "message_type" => env('TERMI_MESSAGE_TYPE'),
            "to" => $user['phone'],
            "from" => env('TERMII_FROM'),
            "channel" => env('TERMII_CHANNEL'),
            "pin_attempts" => env('TERMII_PIN_ATTEMPT'),
            "pin_time_to_live" => env('TERMII_PIN_TIME_TO_LIVE'),
            "pin_length" => env('TERMII_PIN_LENGTH'),
            "pin_placeholder" => "< 1234 >",
            "message_text" => "Your verification pin for Swifthrive is < 1234 >. Pin expires in 30 minutes",
            "pin_type" => env('TERMII_PIN_TYPE'),
        ])->json();
    }

    public static function sendSummonSMS($sender, $recipient, $trade)
    {
        Http::post('https://termii.com/api/sms/send', [
            "api_key" => env('TERMI_KEY'),
            "to" => $recipient['phone'],
            "from" => env('TERMII_FROM'),
            "type" => 'plain',
            "channel" => env('TERMII_CHANNEL'),
            "sms" => $sender['username'].' has summoned you to a trade of '.$trade["amount"].' '.strtoupper($trade->coin['abbr']).' on Swifthrive.',
        ])->json();
    }
}
