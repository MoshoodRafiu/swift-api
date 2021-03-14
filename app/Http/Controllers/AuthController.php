<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuthResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(): \Illuminate\Http\JsonResponse
    {
        $credentials = request(['email', 'username', 'password', 'confirm_password']);

        $validator = Validator::make($credentials, [
            'email' => ['required', 'unique:users,email', 'max:255'],
            'username' => ['required', 'unique:users,username', 'max:255'],
            'password' => ['required', 'same:confirm_password', 'min:8']
        ]);

        if ($validator->fails()){
            return response()->json($validator->messages(), 422);
        }

        $credentials['password'] = Hash::make($credentials['password']);
        $credentials['code'] = $this->generateUserCode();
        $otp = sha1($credentials['email'].time());
        $credentials['email_verification_token'] = Hash::make($otp);
        $credentials['email_verification_token_expiry'] = date('Y-m-d H:i:s', strtotime(now().' + 1 hour'));
        $user = User::create($credentials);
        $token = auth('api')->attempt(request(['email', 'password']));

        if (!($user && $token)){
            return response()->json(['error' => 'Something went wrong'], 400);
        }
        try {
            MailController::sendSignUpEmail($user, $otp);
        }catch (\Exception $exception){
            return response()->json(['error' => 'Something went wrong'], 400);
        }
        try {
            WalletController::generateWallets($user);
        }catch (\Exception $exception){
            return response()->json(['error' => 'Something went wrong'], 400);
        }

        return $this->respondWithTokenAndUser($user, $token);
    }

    public function login(): \Illuminate\Http\JsonResponse
    {
        $credentials = request(['email', 'password']);

        $validator = Validator::make($credentials, [
            'email' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()){
            return response()->json($validator->messages(), 422);
        }

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid login credentials'], 401);
        }

        return $this->respondWithTokenAndUser(User::all()->where('email',$credentials['email'])->first(), $token);
    }

    public function me(): \Illuminate\Http\JsonResponse
    {
        return response()->json(new AuthResource(auth()->user()));
    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh(): \Illuminate\Http\JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithTokenAndUser($user, $token): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'user' => new AuthResource($user),
            'access_token' => $token,
            'token_type' => 'bearer'
        ]);
    }

    protected function respondWithToken($token): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer'
        ]);
    }

    protected function generateUserCode(): string
    {
        return 'SWF'.$this->formatNumber(User::all()->count() + 1);
    }

    protected function formatNumber($num): string
    {
        $len = strlen($num);
        $str = null;
        for ($i = 8; $i < 9; $i--){
            if ($i == $len) {
                $str .= $num;
                break;
            }else{
                $str .= '0';
            }
        }
        return $str;
    }
}
