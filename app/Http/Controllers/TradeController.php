<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\Request;

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
}
