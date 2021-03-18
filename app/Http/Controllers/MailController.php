<?php

namespace App\Http\Controllers;

use App\Mail\SignUpMail;
use App\Mail\SummonMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public static function sendSignUpEmail($user, $otp)
    {
        $data = [
            "username" => $user['username'],
            "link" => env('SPA_URL').'/email/verify?email='.$user['email'].'&otp='.$otp
        ];
        Mail::to($user['email'])->send(new SignUpMail($data));
    }

    public static function sendSummonEmail($sender, $recipient, $trade, $link)
    {
        $data = [
            "sender" => $sender,
            "recipient" => $recipient,
            "trade" => $trade,
            "link" => env('SPA_URL').$link
        ];
        Mail::to($recipient['email'])->send(new SummonMail($data));
    }
}
