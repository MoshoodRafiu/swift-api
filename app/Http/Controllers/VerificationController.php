<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{
    public static function emailSend($user): \Illuminate\Http\JsonResponse
    {
        if ((new VerificationController)->userAlreadyVerified($user,'email')){
            return response()->json(['error' => 'Email already verified'],400);
        }

    }

    public function emailVerify(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if (!$this->userAlreadyVerified($user,'email')){
            return response()->json(['error' => 'Email already verified'],400);
        }
//        Send email
    }

    public function phoneSend(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if ($this->userAlreadyVerified($user,'phone')){
            return response()->json(['error' => 'Phone already verified'],400);
        }
        if (! $user['phone']){
            return response()->json(['error' => 'Update profile before phone verification'] ,400);
        }
        try {
            $res = Http::post('https://termii.com/api/sms/otp/send', [
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
        }catch (\Exception $exception){
            return response()->json(['error' => 'Error connecting to verification server'],500);
        }
        if (!array_key_exists("pinId", $res)){
            return response()->json(['error' => 'Error sending OTP, please contact administrator'],400);
        }
        session()->put('id', $res["pinId"]);
        return response()->json([
            'message' => 'OTP has been sent to '.$user['phone'],
            'data' => $res['pinId']
        ]);
    }

    public function phoneResend(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if (!$this->userAlreadyVerified($user,'phone')){
            return response()->json(['error' => 'Phone already verified'],400);
        }
        if (! $user['phone']){
            return response()->json(['error' => 'Update profile before phone verification'] ,400);
        }
    }

    public function phoneVerify(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "verification_id" => ['required', 'string'],
            "code" => ['required', 'string', 'min:6', 'max:6']
        ]);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        $user = auth()->user();
        if ($this->userAlreadyVerified($user,'phone')){
            return response()->json(['error' => 'Phone already verified'],400);
        }
        if (! $user['phone']){
            return response()->json(['error' => 'Update profile before phone verification'] ,400);
        }
        try {
            $response = Http::post('https://termii.com/api/sms/otp/verify', [
                "api_key" => env('TERMI_KEY'),
                "pin_id" => $request['verification_id'],
                "pin" => $request['code'],
            ])->json();
        }catch (\Exception $exception){
            return response()->json(['error' => 'Error connecting to verification server'],500);
        }
        if (array_key_exists("verified", $response)){
            if ($response['verified'] === true){
                if ($user->verification()->first()){
                    $user->verification()->update(['phone' => true, 'phone_ver_at' => now()]);
                }else{
                    $user->verification()->create(['phone' => true, 'phone_ver_at' => now()]);
                }
                return response()->json(['message' => 'Phone number verified']);
            }elseif ($response['verified'] === "Expired"){
                return response()->json(['error' => 'Pin already expired'] ,400);
            }
        }

        if (array_key_exists("attemptsRemaining", $response)){
            return response()->json(['error' => 'Incorrect OTP, '.$response['attemptsRemaining'].' attempts remaining'] ,400);
        }
        return response()->json(['error' => 'Error verifying phone'] ,400);
    }

    public function documentUpload(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if (!$this->userAlreadyVerified($user,'kyc')){
            return response()->json(['error' => 'KYC verification already completed'],400);
        }
    }

    protected function userAlreadyVerified($user, $type): bool
    {
        switch ($type){
            case "email":
                switch (true){
                    case ($user->verification && $user->verification['email']):
                        return true;
                }
                break;
            case "phone":
                switch (true){
                    case ($user->verification && $user->verification['phone']):
                        return true;
                }
                break;
            case "kyc":
                switch (true){
                    case ($user->verification && $user->verification['kyc']):
                        return true;
                }
                break;
        }
        return false;
    }
}
