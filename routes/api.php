<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdvertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionPinController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('verification/email/verify', [VerificationController::class, 'emailVerify']);

Route::middleware('auth:api')->group(function (){
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);

    Route::get('adverts/filter/{coin}/{type}', [AdvertController::class, 'index']);
    Route::get('user/adverts', [AdvertController::class, 'userAdverts']);
    Route::post('adverts/store', [AdvertController::class, 'store']);
    Route::get('adverts/{advert}/show', [AdvertController::class, 'show']);
    Route::put('adverts/{advert}/update', [AdvertController::class, 'update']);
    Route::delete('adverts/{advert}/destroy', [AdvertController::class, 'destroy']);

    Route::post('profile/update', [MainController::class, 'updateProfile']);
    Route::post('password/update', [MainController::class, 'updatePassword']);

    Route::post('pin/store', [TransactionPinController::class, 'store']);
    Route::post('pin/update', [TransactionPinController::class, 'update']);

    Route::post('verification/email/resend', [VerificationController::class, 'emailResend']);
    Route::post('verification/phone/send', [VerificationController::class, 'phoneSend']);
    Route::post('verification/phone/verify', [VerificationController::class, 'phoneVerify']);
    Route::post('verification/document/upload', [VerificationController::class, 'documentUpload']);

    Route::get('trades/{type}/fetch', [TradeController::class, 'index']);
    Route::get('trades/{trade}/show', [TradeController::class, 'show']);
    Route::post('trades/initiate', [TradeController::class, 'initiate']);
    Route::put('trades/{trade}/buyer/{level}/process', [TradeController::class, 'buyerProcess']);
    Route::put('trades/{trade}/seller/{level}/process', [TradeController::class, 'sellerProcess']);
    Route::put('trades/{trade}/coin/release', [TradeController::class, 'releaseCoin']);
    Route::put('trades/{trade}/summon/{type}/email', [TradeController::class, 'summonTraderViaEmail']);
    Route::put('trades/{trade}/summon/{type}/sms', [TradeController::class, 'summonTraderViaSMS']);
    Route::put('trades/{trade}/rate', [TradeController::class, 'rateUser']);

    Route::get('trades/{trade}/chat', [ChatController::class, 'index']);
    Route::post('trades/chat', [ChatController::class, 'store']);

    Route::post('transaction/withdraw', [TransactionController::class, 'withdraw']);

    Route::get('wallets', [WalletController::class, 'index']);
    Route::get('wallets/{coin}/show', [WalletController::class, 'show']);
});

Route::prefix('admin/')->middleware(['adminOnly'])->group(function (){
    Route::get('dashboard', [AdminController::class, 'index']);

    Route::get('trades', [TradeController::class, 'index']);
});

Route::prefix('admin/')->middleware(['adminOrAgent'])->group(function (){

});
