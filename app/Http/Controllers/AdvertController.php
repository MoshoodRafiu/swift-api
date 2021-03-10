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

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string'],
            'coin_id' => ['required', 'numeric'],
            'min' => ['required', 'numeric', 'lt:max'],
            'max' => ['required', 'numeric', 'gt:min'],
            'rate' => ['required', 'numeric'],
            'duration' => ['required', 'string'],
            'active' => ['required', 'boolean'],
        ],
        [
            'min.lt' => 'The from amount must be greater than the to amount',
            'max.gt' => 'The to amount must be less than the from amount',
        ]);

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
        return response()->json([
            'message' => 'Advert created successfully',
            'data' => $advert
        ], 400);
    }
}
