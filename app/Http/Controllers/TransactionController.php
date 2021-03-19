<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function withdraw(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "source" => ['required', 'string'],
            "destination" => ['required', 'string'],
            "amount" => ['required', 'numeric'],
        ]);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        $user = auth()->user();
        $wallet = $user->wallets()->where('address', $request['source'])->first();
        $coin = $wallet->coin;
        if (!WalletController::walletBalanceVerifiedForUser($user, $coin, $request['amount'])){
            return response()->json(['error' => 'Insufficient wallet balance']);
        }
    }
}
