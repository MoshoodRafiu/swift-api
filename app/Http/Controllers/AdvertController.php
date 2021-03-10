<?php

namespace App\Http\Controllers;

use App\Models\Advert;
use App\Models\Coin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdvertController extends Controller
{
    public function index($coin, $type): \Illuminate\Http\JsonResponse
    {
        $coin = Coin::all()
                        ->where('abbr', $coin)
                        ->first();
        if (!$coin) {
            return response()->json(['error' => 'Coin not found'], 400);
        }
        return response()->json([
            'data' => $coin->adverts()
                            ->where('type', $type)
                            ->latest()
                            ->orderBy('rate', 'desc')
                            ->get()
        ]);
    }

    public function userAdverts(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => auth()
                        ->user()
                        ->adverts()
                        ->get()
        ]);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        $coin = Coin::find($request['coin_id']);
        if (!$coin) {
            return response()->json(['error' => 'Coin not found'], 400);
        }
        $request['coin_id'] = $coin['id'];
        $request['user_id'] = auth()->user()['id'];
        if (!$advert = Advert::create($request->all())){
            return response()->json(['error' => 'Something went wrong'], 400);
        }
        $advert->bank()->create($request->only(['bank_name', 'account_name', 'account_number']));
        return response()->json([
            'message' => 'Advert created successfully',
            'data' => $advert
        ]);
    }

    public function show(Advert $advert): \Illuminate\Http\JsonResponse
    {
        if (! $this->advertBelongsToUser($advert)){
            return response()->json(['error' => 'Access Restricted'], 400);
        }
        return response()->json([
            'data' => $advert
        ]);
    }

    public function update(Advert $advert, Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        if (! $this->advertBelongsToUser($advert)){
            return response()->json(['error' => 'Access Restricted'], 400);
        }
        if (!$advert->update($request->only(['type', 'min', 'max', 'rate', 'duration', 'active']))){
            return response()->json(['error' => 'Something went wrong'], 400);
        }
        $advert->bank()->update($request->only(['bank_name', 'account_name', 'account_number']));
        return response()->json([
            'message' => 'Advert update successfully',
            'data' => $advert
        ]);
    }

    public function destroy(Advert $advert): \Illuminate\Http\JsonResponse
    {
        if (! $this->advertBelongsToUser($advert)){
            return response()->json(['error' => 'Access Restricted'], 400);
        }
        if (!$advert->bank()->delete() || !$advert->delete()){
            return response()->json(['error' => 'Something went wrong'], 400);
        }
        return response()->json([
            'message' => 'Advert deleted successfully',
        ]);
    }

    protected function validateRequest($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'type' => ['required', 'string'],
            'coin_id' => ['required', 'numeric'],
            'min' => ['required', 'numeric', 'lt:max'],
            'max' => ['required', 'numeric', 'gt:min'],
            'rate' => ['required', 'numeric'],
            'duration' => ['required', 'string'],
            'active' => ['required', 'boolean'],
            'bank_name' => ['required', 'string'],
            'account_name' => ['required', 'string'],
            'account_number' => ['required', 'string']
        ],
        [
            'min.lt' => 'The from amount must be greater than the to amount',
            'max.gt' => 'The to amount must be less than the from amount',
        ]);
    }

    protected function advertBelongsToUser($advert): bool
    {
        return auth()->user()->adverts()->whereId($advert['id'])->count() > 0;
    }
}
