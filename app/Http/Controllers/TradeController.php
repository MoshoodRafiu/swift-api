<?php

namespace App\Http\Controllers;

use App\Models\Advert;
use App\Models\Coin;
use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TradeController extends Controller
{
    public function index($type): \Illuminate\Http\JsonResponse
    {
        $trades = Trade::query()->where(function ($q){
            $q->where('buyer_id', auth()->user()['id'])
                ->orWhere('seller_id', auth()->user()['id']);});
        switch ($type){
            case "pending":
                $trades = $trades->where('status', 0)->get();
                break;
            case "success":
                $trades = $trades->where('status', 1)->get();
                break;
            case "cancelled":
                $trades = $trades->where('status', 2)->get();
                break;
            default:
                $trades = $trades->get();
        }
        return response()->json([
            'data' => $trades
        ]);
    }

    public function show(Trade $trade): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => $trade
        ]);
    }

    public function initiate(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'advert_id' => ['required'],
            'amount' => ['required', 'numeric'],
        ]);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        $user = auth()->user();
        $advert = Advert::find($request['advert_id']);
        $coin = $advert->coin;
        if ((!$advert) || ($advert->user['id'] == $user['id'])){
            return response()->json(['error' => 'There was an error starting this trade'], 400);
        }
        if (($request['amount'] < $advert['min']) || ($request['amount'] > $advert['max'])){
            return response()->json(['error' => 'There amount must be between '.$advert['min'].' and '.$advert['max']], 400);
        }
        $trade = $coin->trades()->create([
            'buyer_id' => $advert['type'] === 'buy' ? $advert->user['id'] : $user['id'],
            'seller_id' => $advert['type'] === 'sell' ? $advert->user['id'] : $user['id'],
            'amount' => $request['amount'],
            'amount_usd' => $this->getAmountInUSD($request['amount']),
            'amount_ngn' => $this->getAmountInUSD($request['amount']) * $advert['rate'],
        ]);
        return response()->json([
            'message' => 'Trade initiated successfully',
            'data' => $trade
        ]);
    }

    protected function getAmountInUSD($amount)
    {
        return $amount * 23456;
    }

    protected function getUserRoleInTrade($user, $advert): ?string
    {
        $role = null;
        if ($advert->user['id'] == $user['id']){
            switch ($advert['type']){
                case 'buy':
                    $role = 'buyer';
                    break;
                case 'sell':
                    $role = 'seller';
                    break;
            }
        }else{
            switch ($advert['type']){
                case 'buy':
                    $role = 'seller';
                    break;
                case 'sell':
                    $role = 'buyer';
                    break;
            }
        }
        return $role;
    }
}
