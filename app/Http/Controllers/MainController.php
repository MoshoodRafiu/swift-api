<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MainController extends Controller
{
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'other_name' => ['required', 'string'],
            'phone' => ['required', 'string']
        ]);

        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }

        auth()->user()->update($request->only(['first_name', 'last_name', 'other_name', 'phone']));

    }
}
