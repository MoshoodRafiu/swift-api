<?php

namespace App\Http\Controllers;

use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;
use App\Models\Coin;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class WalletController extends Controller
{
    public static function generateWallets($user)
    {
        self::generateBitcoinAddress($user);
        self::generateEthereumAddress($user);
        self::generateBitcoinCashAddress($user);
        self::generateLitecoinAddress($user);
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['data' => WalletResource::collection(auth()->user()->wallets()->get())]);
    }

    public function show($coin): \Illuminate\Http\JsonResponse
    {
        $coin = Coin::all()->where('abbr', $coin)->first();
        if (!$coin){
            return response()->json(['error' => 'Coin not found'], 400);
        }
        return response()->json(['data' => new WalletTransactionResource($coin->wallets()->where('user_id', auth()->user()['id'])->first())]);
    }

    protected static function generateBitcoinAddress($user){
        $btc_address = Http::withHeaders([
            'Content-type' => 'application/json', //Content-Type: application/json
            'X-API-Key' => env('CRYPTO_APP_KEY')
        ])->post('https://api.cryptoapis.io/v1/bc/btc/testnet/address')->json();

        $coin = Coin::all()->where('name', 'Bitcoin')->first();
        $wallet = Wallet::create([
            'coin_id' => $coin['id'],
            'user_id' => $user['id'],
            'address' => $btc_address['payload']['address'],
            'balance' => 0.0000000
        ]);

        $wallet->credential()->create([
            'key' => Crypt::encryptString($btc_address['payload']['wif']),
            'stuprk' => Crypt::encryptString($btc_address['payload']['privateKey']),
            'stupuk' => Crypt::encryptString($btc_address['payload']['publicKey']),
        ]);
    }
    protected static function generateEthereumAddress($user){
        $eth_address = Http::withHeaders([
            'Content-type' => 'application/json', //Content-Type: application/json
            'X-API-Key' => env('CRYPTO_APP_KEY')
        ])->post('https://api.cryptoapis.io/v1/bc/eth/ropsten/address')->json();

        $coin = Coin::all()->where('name', 'Ethereum')->first();

        $wallet = Wallet::create([
            'coin_id' => $coin['id'],
            'user_id' => $user['id'],
            'address' => $eth_address['payload']['address'],
            'balance' => 0.0000000
        ]);

        $wallet->credential()->create([
            'key' => Crypt::encryptString($eth_address['payload']['privateKey']),
            'stuprk' => Crypt::encryptString($eth_address['payload']['privateKey']),
            'stupuk' => Crypt::encryptString($eth_address['payload']['publicKey']),
        ]);
    }
    protected static function generateBitcoinCashAddress($user){
        $bch_address = Http::withHeaders([
            'Content-type' => 'application/json', //Content-Type: application/json
            'X-API-Key' => env('CRYPTO_APP_KEY')
        ])->post('https://api.cryptoapis.io/v1/bc/bch/testnet/address')->json();

        $coin = Coin::all()->where('name', 'Bitcoin Cash')->first();

        $wallet = Wallet::create([
            'coin_id' => $coin['id'],
            'user_id' => $user['id'],
            'address' => $bch_address['payload']['address'],
            'balance' => 0.0000000
        ]);

        $wallet->credential()->create([
            'key' => Crypt::encryptString($bch_address['payload']['wif']),
            'stuprk' => Crypt::encryptString($bch_address['payload']['privateKey']),
            'stupuk' => Crypt::encryptString($bch_address['payload']['publicKey']),
        ]);
    }

    protected static function generateLitecoinAddress($user){
        $ltc_address = Http::withHeaders([
            'Content-type' => 'application/json', //Content-Type: application/json
            'X-API-Key' => env('CRYPTO_APP_KEY')
        ])->post('https://api.cryptoapis.io/v1/bc/ltc/testnet/address')->json();
        $coin = Coin::all()->where('name', 'Litecoin')->first();
        $wallet = Wallet::create([
            'coin_id' => $coin['id'],
            'user_id' => $user['id'],
            'address' => $ltc_address['payload']['address'],
            'balance' => 0.0000000
        ]);
        $wallet->credential()->create([
            'key' => Crypt::encryptString($ltc_address['payload']['wif']),
            'stuprk' => Crypt::encryptString($ltc_address['payload']['privateKey']),
            'stupuk' => Crypt::encryptString($ltc_address['payload']['publicKey']),
        ]);
    }
}
