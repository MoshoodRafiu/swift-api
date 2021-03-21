<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function withdraw(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "coin_id" => ['required'],
            "destination" => ['required', 'string'],
            "amount" => ['required', 'numeric'],
        ]);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        $user = auth()->user();
        $coin = Coin::find($request['coin_id']);
        if (!$coin){
            return response()->json(['error' => 'Coin not supported'], 400);
        }
        $wallet = $user->wallets()->where('coin_id', $coin['id'])->first();
        if (!WalletController::walletBalanceVerifiedForUser($user, $coin, $request['amount'])){
            return response()->json(['error' => 'Insufficient '.$coin['name'].' wallet balance'], 400);
        }
    }
}
