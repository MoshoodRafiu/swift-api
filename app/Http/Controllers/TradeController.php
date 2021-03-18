<?php

namespace App\Http\Controllers;

use App\Http\Resources\TradeResource;
use App\Http\Resources\TransactionResource;
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
            'data' => new TradeResource($trade)
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
        if ((!$advert) || ($advert->user['id'] == $user['id'])){
            return response()->json(['error' => 'There was an error starting this trade'], 400);
        }
        if (($request['amount'] < $advert['min']) || ($request['amount'] > $advert['max'])){
            return response()->json(['error' => 'There amount must be between '.$advert['min'].' and '.$advert['max']], 400);
        }
        $coin = $advert->coin;
        $data = [
            'buyer_id' => $advert['type'] === 'buy' ? $advert->user['id'] : $user['id'],
            'seller_id' => $advert['type'] === 'sell' ? $advert->user['id'] : $user['id'],
            'amount' => $request['amount'],
            'amount_usd' => $this->getAmountInUSD($request['amount']),
            'amount_ngn' => $this->getAmountInUSD($request['amount']) * $advert['rate'],
            'duration' => $request['duration'],
            'window_expiry' => date('Y-m-d H:i:s', strtotime(now().' + '.$advert['duration'].' minutes')),
        ];
        switch ($this->userRoleByAdvert($user, $advert)){
            case "buyer":
                $data['buyer_status'] = 1;
                break;
            case "seller":
                $data['seller_status'] = 1;
                break;
        }
        $trade = $coin->trades()->create($data);
        $trade->payment()->create([
            'bank_name' => $advert->bank['bank_name'],
            'account_name' => $advert->bank['account_name'],
            'account_number' => $advert->bank['account_number'],
        ]);
        return response()->json([
            'message' => 'Trade initiated successfully',
            'data' => new TradeResource($trade)
        ]);
    }

    public function buyerProcess(Trade $trade, $level): \Illuminate\Http\JsonResponse
    {
        if (!$trade['status'] == 0){
            return response()->json(['error' => 'Error processing trade'], 400);
        }
        if ($trade['buyer_id'] != auth()->user()['id']){
            return response()->json(['error' => 'Unauthorized action'], 400);
        }
        $message = null;
        switch ($level){
            case 1:
                if (!$trade['buyer_status'] == 0){
                    return response()->json(['error' => 'Unauthorized action'], 400);
                }
                $message = 'Trade Accepted';
                break;
            case 2:
                if (!($trade['buyer_status'] == 1 && $trade['seller_status'] == 1)){
                    return response()->json(['error' => 'Unauthorized action'], 400);
                }
                $message = 'Payment Made';
                break;
        }
        $trade->update(['buyer_status' => $level]);
        return response()->json([
            'message' => $message,
            'data' => new TradeResource($trade)
        ]);
    }

    public function sellerProcess(Trade $trade, $level): \Illuminate\Http\JsonResponse
    {
        if (!$trade['status'] == 0){
            return response()->json(['error' => 'Error processing trade'], 400);
        }
        if ($trade['seller_id'] != auth()->user()['id']){
            return response()->json(['error' => 'Unauthorized action'], 400);
        }
        $message = null;
        switch ($level){
            case 1:
                if (!$trade['seller_status'] == 0){
                    return response()->json(['error' => 'Unauthorized action'], 400);
                }
                $message = 'Trade Accepted';
                break;
            case 2:
                if (!($trade['buyer_status'] == 2 && $trade['seller_status'] == 1)){
                    return response()->json(['error' => 'Unauthorized action'], 400);
                }
                $message = 'Payment confirmed';
                break;
        }
        $trade->update(['seller_status' => $level]);
        return response()->json([
            'message' => $message,
            'data' => new TradeResource($trade)
        ]);
    }

    public function releaseCoin(Request $request, Trade $trade): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
           'pin' => ['required', 'string', 'min:4', 'max:4']
        ]);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        if (!auth()->user()->pinVerified($request['pin'])){
            return response()->json(['error' => 'Incorrect pin'], 400);
        }
//        release coin function
    }

    public function summonTraderViaEmail(Trade $trade, $type): \Illuminate\Http\JsonResponse
    {
        switch ($type){
            case "buyer":
                if (auth()->user()['id'] != $trade['seller_id']){
                    return response()->json(['error' => 'Buyer summon via mail not allowed'], 400);
                }
                $sender = $trade->seller;
                $recipient = $trade->buyer;
                $link = '/btc/trades/1/sell';
                break;
            case "seller":
                if (auth()->user()['id'] != $trade['buyer_id']){
                    return response()->json(['error' => 'Seller summon via mail not allowed'], 400);
                }
                $sender = $trade->buyer;
                $recipient = $trade->seller;
                $link = '/btc/trades/1/sell';
                break;
            default:
                return response()->json(['error' => 'Summon not allowed'], 400);
        }
        try {
            MailController::sendSummonEmail($sender, $recipient, $trade, $link);
        }catch (\Exception $exception){
            return response()->json(['error' => 'Error summoning via email'], 400);
        }
        return response()->json([
            'success' => 'Summon email sent',
            'data' => $trade
        ]);
    }

    public function summonTraderViaSMS(Trade $trade, $type): \Illuminate\Http\JsonResponse
    {
        switch ($type){
            case "buyer":
                if (auth()->user()['id'] != $trade['seller_id']){
                    return response()->json(['error' => 'Buyer summon via SMS not allowed'], 400);
                }
                if ($trade['seller_has_summoned'] > 0){
                    return response()->json(['error' => 'Summon via SMS can only be made once'], 400);
                }
                $sender = $trade->seller;
                $recipient = $trade->buyer;
                $update = ['seller_has_summoned' => 1];
                break;
            case "seller":
                if (auth()->user()['id'] != $trade['buyer_id']){
                    return response()->json(['error' => 'Seller summon via SMS not allowed'], 400);
                }
                if ($trade['buyer_has_summoned'] > 0){
                    return response()->json(['error' => 'Summon via SMS can only be made once'], 400);
                }
                $sender = $trade->buyer;
                $recipient = $trade->seller;
                $update = ['buyer_has_summoned' => 1];
                break;
            default:
                return response()->json(['error' => 'Summon not allowed'], 400);
        }
        try {
            SMSController::sendSummonSMS($sender, $recipient, $trade);
            $trade->update($update);
        }catch (\Exception $exception){
            return response()->json(['error' => 'Error summoning via email'], 400);
        }
        return response()->json([
            'success' => 'Summon SMS sent',
            'data' => $trade
        ]);
    }

    protected function getAmountInUSD($amount)
    {
        return $amount * 23456;
    }

    public function userRoleByAdvert($user, $advert): ?string
    {
        $role = null;
        switch ($advert['type']){
            case "buy":
                if ($user['id'] == $advert->user['id']){
                    $role = 'buyer';
                }else{
                    $role = 'seller';
                }
                break;
            case "sell":
                if ($user['id'] == $advert->user['id']){
                    $role = 'seller';
                }else{
                    $role = 'buyer';
                }
                break;
        }
        return $role;
    }

    public function userRoleByTrade($user, $trade): ?string
    {
        $role = null;
        switch ($user['id']){
            case $trade['buyer_id']:
                $role = 'buyer';
                break;
            case $trade['seller_id']:
                $role = 'seller';
                break;
        }
        return $role;
    }
}
