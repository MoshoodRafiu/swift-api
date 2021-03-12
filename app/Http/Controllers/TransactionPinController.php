<?php

namespace App\Http\Controllers;

use App\Models\TransactionPin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TransactionPinController extends Controller
{
    protected $lessSecuredPins = ["1234", "1111", "0000"];
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pin' => ['required', 'string', 'min:4', 'max:4', 'same:confirm_pin']
        ]);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        if (auth()->user()->pin()->count() > 0){
            return response()->json(['error' => 'Pin already created, update pin instead'], 400);
        }
        if (!$this->pinIsSecured($request['pin'])){
            return response()->json(['error' => 'Enter a more secured pin']);
        }
        auth()->user()->pin()->create(['pin' => Hash::make($request['pin'])]);
        return response()->json(['message' => 'Pin created successfully']);
    }

    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'old_pin' => ['required', 'string'],
            'new_pin' => ['required', 'string', 'min:4', 'max:4', 'same:confirm_pin'],
        ]);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        if (auth()->user()->pin()->count() == 0){
            return response()->json(['error' => 'Pin not created, create pin instead'], 400);
        }
        if (!$this->pinIsSecured($request['new_pin'])){
            return response()->json(['error' => 'Enter a more secured pin']);
        }
        if (!Hash::check($request['old_pin'], auth()->user()->pin()->first()['pin'])){
            return response()->json(['error' => 'Old pin incorrect'], 400);
        }
        if (Hash::check($request['new_pin'], auth()->user()->pin()->first()['pin'])){
            return response()->json(['error' => 'Please enter a different new pin'], 400);
        }
        auth()->user()->pin()->update(['pin' => Hash::make($request['new_pin'])]);
        return response()->json(['message' => 'Pin updated successfully']);
    }

    protected function pinIsSecured($pin): bool
    {
        $secured = true;
        foreach ($this->lessSecuredPins as $lessSecuredPin)
        {
            if ($pin == $lessSecuredPin){
                $secured = false;
                break;
            }
        }
        return $secured;
    }
}
