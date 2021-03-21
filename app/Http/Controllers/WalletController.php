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
        self::generateBitcoinWallet($user);
        self::generateEthereumWallet($user);
        self::generateBitcoinCashWallet($user);
        self::generateLitecoinWallet($user);
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

    public static function walletBalanceVerifiedForUser($user, $coin, $amount): bool
    {
        $balance = 0;
        $wallet = $user->wallets()->where('coin_id', $coin['id'])->first();
        $address = $wallet->addresses()->first()['address'];
        switch ($coin['abbr']){
            case "btc":
                $balance = self::getBitcoinWalletBalance($address);
                break;
            case "eth":
                $balance = self::getEthereumWalletBalance($address);
                break;
            case "bch":
                $balance = self::getBitcoinCashWalletBalance($address);
                break;
            case "ltc":
                $balance = self::getLitecoinWalletBalance($address);
                break;
        }
        return $balance >= $amount;
    }

    protected static function generateBitcoinWallet($user){
        $btc_wallet = Http::withHeaders([
            'Content-type' => 'application/json', //Content-Type: application/json
            'X-API-Key' => env('CRYPTO_APP_KEY')
        ])->post('https://rest.cryptoapis.io/v2/blockchain-tools/bitcoin/'.env('CRYPTO_NET_1').'/addresses/generate')->json();

        $coin = Coin::all()->where('name', 'Bitcoin')->first();
        $wallet = Wallet::create([
            'coin_id' => $coin['id'],
            'user_id' => $user['id'],
            'balance' => 0.0000000
        ]);

        self::storeAddressesByFormatForWallet($wallet, $btc_wallet['data']['item']['addresses']);

        $wallet->credential()->create([
            'key' => Crypt::encryptString($btc_wallet['data']['item']['wif']),
            'stuprk' => Crypt::encryptString($btc_wallet['data']['item']['privateKey']),
            'stupuk' => Crypt::encryptString($btc_wallet['data']['item']['publicKey']),
        ]);
    }

    protected static function getBitcoinWalletBalance($address)
    {
        return Http::withHeaders([
            'Content-type' => 'application/json', //Content-Type: application/json
            'X-API-Key' => env('CRYPTO_APP_KEY')])
            ->get('https://rest.cryptoapis.io/v2/blockchain-data/bitcoin/'.env('CRYPTO_NET_1').'/addresses/'.$address)
            ->json()['data']['item']['confirmedBalance']['amount'];
    }

    protected static function generateEthereumWallet($user){
        $eth_wallet = Http::withHeaders([
            'Content-type' => 'application/json', //Content-Type: application/json
            'X-API-Key' => env('CRYPTO_APP_KEY')
        ])->post('https://rest.cryptoapis.io/v2/blockchain-tools/ethereum/'.env('CRYPTO_NET_2').'/addresses/generate')->json();

        $coin = Coin::all()->where('name', 'Ethereum')->first();

        $wallet = Wallet::create([
            'coin_id' => $coin['id'],
            'user_id' => $user['id'],
            'balance' => 0.0000000
        ]);

        self::storeAddressesByFormatForWallet($wallet, $eth_wallet['data']['item']['addresses']);

        $wallet->credential()->create([
            'key' => Crypt::encryptString($eth_wallet['data']['item']['privateKey']),
            'stuprk' => Crypt::encryptString($eth_wallet['data']['item']['privateKey']),
            'stupuk' => Crypt::encryptString($eth_wallet['data']['item']['publicKey']),
        ]);
    }

    protected static function getEthereumWalletBalance($address)
    {
        return Http::withHeaders([
            'Content-type' => 'application/json', //Content-Type: application/json
            'X-API-Key' => env('CRYPTO_APP_KEY')])
            ->get('https://rest.cryptoapis.io/v2/blockchain-data/ethereum/'.env('CRYPTO_NET_2').'/addresses/'.$address)
            ->json()['data']['item']['confirmedBalance']['amount'];
    }

    protected static function generateBitcoinCashWallet($user){
        $bch_wallet = Http::withHeaders([
            'Content-type' => 'application/json', //Content-Type: application/json
            'X-API-Key' => env('CRYPTO_APP_KEY')
        ])->post('https://rest.cryptoapis.io/v2/blockchain-tools/bitcoin-cash/'.env('CRYPTO_NET_1').'/addresses/generate')->json();

        $coin = Coin::all()->where('name', 'Bitcoin Cash')->first();

        $wallet = Wallet::create([
            'coin_id' => $coin['id'],
            'user_id' => $user['id'],
            'balance' => 0.0000000
        ]);

        self::storeAddressesByFormatForWallet($wallet, $bch_wallet['data']['item']['addresses']);

        $wallet->credential()->create([
            'key' => Crypt::encryptString($bch_wallet['data']['item']['wif']),
            'stuprk' => Crypt::encryptString($bch_wallet['data']['item']['privateKey']),
            'stupuk' => Crypt::encryptString($bch_wallet['data']['item']['publicKey']),
        ]);
    }

    protected static function getBitcoinCashWalletBalance($address)
    {
        return Http::withHeaders([
            'Content-type' => 'application/json', //Content-Type: application/json
            'X-API-Key' => env('CRYPTO_APP_KEY')])
            ->get('https://rest.cryptoapis.io/v2/blockchain-data/bitcoin-cash/'.env('CRYPTO_NET_1').'/addresses/'.$address)
            ->json()['data']['item']['confirmedBalance']['amount'];
    }

    protected static function generateLitecoinWallet($user){
        $ltc_wallet = Http::withHeaders([
            'Content-type' => 'application/json', //Content-Type: application/json
            'X-API-Key' => env('CRYPTO_APP_KEY')
        ])->post('https://rest.cryptoapis.io/v2/blockchain-tools/litecoin/'.env('CRYPTO_NET_1').'/addresses/generate')->json();
        $coin = Coin::all()->where('name', 'Litecoin')->first();
        $wallet = Wallet::create([
            'coin_id' => $coin['id'],
            'user_id' => $user['id'],
            'balance' => 0.0000000
        ]);

        self::storeAddressesByFormatForWallet($wallet, $ltc_wallet['data']['item']['addresses']);

        $wallet->credential()->create([
            'key' => Crypt::encryptString($ltc_wallet['data']['item']['wif']),
            'stuprk' => Crypt::encryptString($ltc_wallet['data']['item']['privateKey']),
            'stupuk' => Crypt::encryptString($ltc_wallet['data']['item']['publicKey']),
        ]);
    }

    protected static function getLitecoinWalletBalance($address)
    {
        return Http::withHeaders([
            'Content-type' => 'application/json', //Content-Type: application/json
            'X-API-Key' => env('CRYPTO_APP_KEY')])
            ->get('https://rest.cryptoapis.io/v2/blockchain-data/litecoin/'.env('CRYPTO_NET_1').'/addresses/'.$address)
            ->json()['data']['item']['confirmedBalance']['amount'];
    }

    protected static function storeAddressesByFormatForWallet($wallet, $addresses)
    {
        foreach ($addresses as $address){
            $wallet->addresses()->create([
                'format' => $address['format'],
                'address' => $address['address']
            ]);
        }
    }
}
