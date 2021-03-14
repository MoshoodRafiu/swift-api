<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{
    public function emailResend(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if ($this->userAlreadyVerified($user,'email')){
            return response()->json(['error' => 'Email already verified'],400);
        }
        $otp = sha1($user['email'].time());
        $user->update([
            'email_verification_token' => Hash::make($otp),
            'email_verification_token_expiry' => date('Y-m-d H:i:s', strtotime(now().' + 1 hour'))
        ]);
        try {
            MailController::sendSignUpEmail($user, $otp);
        }catch (\Exception $exception){
            return response()->json(['error' => 'Something went wrong'], 400);
        }
        return response()->json(['message' => 'Email verification link resent to '.$user['email']]);
    }

    public function emailVerify(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "token" => ['required', 'string'],
            "email" => ['required', 'string']
        ]);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        $user = User::all()->where('email', $request['email'])->first();
        if ($this->userAlreadyVerified($user,'email')){
            return response()->json(['error' => 'Email already verified'],400);
        }
        if (strtotime($user['email_verification_token_expiry']) < strtotime(now())){
            return response()->json(['error' => 'Email verification token expired'],400);
        }
        if (!Hash::check($request['token'], $user['email_verification_token'])){
            return response()->json(['error' => 'Email verification token invalid'],400);
        }
        if ($user->verification()->first()){
            $user->verification()->update(['email' => true, 'email_ver_at' => now()]);
        }else{
            $user->verification()->create(['email' => true, 'email_ver_at' => now()]);
        }
        return response()->json(['message' => 'Email verified']);
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
        $validator = Validator::make($request->all(), [
            "file" => ['required', 'file'],
            "type" => ['required', 'string']
        ]);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        $user = auth()->user();
        if ($this->userAlreadyVerified($user,'kyc')){
            return response()->json(['error' => 'KYC verification already completed'],400);
        }
        $path = 'documents';
        $transferDoc = $user['code'].'-'. time() . '.' . $request['file']->getClientOriginalExtension();
        $request['file']->move($path, $transferDoc);
        $doc = $user->documents()->create(['type' => $request['type'], 'url' => $path."/".$transferDoc]);
        return response()->json([
            'message' => 'Document uploaded successfully',
            'data' => $doc
        ]);
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
