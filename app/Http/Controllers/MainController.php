<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MainController extends Controller
{
    public function updateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'other_name' => ['sometimes', 'string'],
            'phone' => ['required', 'string']
        ]);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        auth()->user()->update($request->only(['first_name', 'last_name', 'other_name', 'phone']));
        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => auth()->user()
        ]);
    }
    public function updatePassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'same:confirm_password'],
        ]);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        if (!Hash::check($request['old_password'], auth()->user()['password'])){
            return response()->json(['error' => 'Old password incorrect'], 400);
        }
        if (Hash::check($request['new_password'], auth()->user()['password'])){
            return response()->json(['error' => 'Please enter a different new password'], 400);
        }
        auth()->user()->update(['password' => Hash::make($request['new_password'])]);
        return response()->json([
            'message' => 'Password updated successfully',
            'data' => auth()->user()
        ]);
    }
}
