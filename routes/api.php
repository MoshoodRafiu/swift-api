<?php

use App\Http\Controllers\AdvertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\TransactionPinController;
use App\Models\Verification;
use Illuminate\Http\Request;
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

    Route::post('verification/email/send', [Verification::class, 'emailSend']);
    Route::post('verification/email/resend', [Verification::class, 'emailResend']);
    Route::post('verification/email/verify', [Verification::class, 'emailVerify']);
    Route::post('verification/phone/send', [Verification::class, 'phoneSend']);
    Route::post('verification/phone/resend', [Verification::class, 'phoneResend']);
    Route::post('verification/phone/verify', [Verification::class, 'phoneVerify']);
    Route::post('verification/document/upload', [Verification::class, 'documentUpload']);

    Route::get('trades/{type}/fetch', [TradeController::class, 'index']);
    Route::get('trades/{trade}/show', [TradeController::class, 'show']);
});
