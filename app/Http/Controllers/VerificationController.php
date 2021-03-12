<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public static function emailSend($user): \Illuminate\Http\JsonResponse
    {
        if (!(new VerificationController)->userAlreadyVerified($user,'email')){
            return response()->json(['error' => 'Email already verified']);
        }
//        Send email
    }

    public function emailVerify(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if (!$this->userAlreadyVerified($user,'email')){
            return response()->json(['error' => 'Email already verified']);
        }
//        Send email
    }

    public function phoneSend(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if (!$this->userAlreadyVerified($user,'phone')){
            return response()->json(['error' => 'Phone already verified']);
        }
    }

    public function phoneResend(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if (!$this->userAlreadyVerified($user,'phone')){
            return response()->json(['error' => 'Phone already verified']);
        }
    }

    public function phoneVerify(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if (!$this->userAlreadyVerified($user,'phone')){
            return response()->json(['error' => 'Phone already verified']);
        }
    }

    public function documentUpload(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if (!$this->userAlreadyVerified($user,'kyc')){
            return response()->json(['error' => 'KYC verification already completed']);
        }
    }

    protected function userAlreadyVerified($user, $type): bool
    {
        switch ($type){
            case "email":
                return !!($user->verification()->count() > 0 && $user->verification()['email']);
            case "phone":
                return !!($user->verification()->count() > 0 && $user->verification()['phone']);
            case "kyc":
                return !!($user->verification()->count() > 0 && $user->verification()['kyc']);
        }
        return false;
    }
}
