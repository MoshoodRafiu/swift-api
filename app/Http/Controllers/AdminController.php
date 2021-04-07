<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Trade;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(): array
    {
        return [
            'revenue' => 100,
            'success_txt' => Trade::all()->where('status', 1)->count(),
            'pending_txt' => Trade::all()->where('status', 0)->count(),
            'cancelled_txt' => Trade::all()->where('status', 2)->count(),
            'yesterday_txt' => Trade::whereDate('created_at', date('Y-m-d', strtotime(now().' - 1 day')))->count(),
            'today_txt' => Trade::whereDate('created_at', date('Y-m-d', strtotime(now())))->count(),
            'chart_data' => [
                'success' => [
                    Trade::whereMonth('created_at', 1)->where('status', 1)->count(),
                    Trade::whereMonth('created_at', 2)->where('status', 1)->count(),
                    Trade::whereMonth('created_at', 3)->where('status', 1)->count(),
                    Trade::whereMonth('created_at', 4)->where('status', 1)->count(),
                    Trade::whereMonth('created_at', 5)->where('status', 1)->count(),
                    Trade::whereMonth('created_at', 6)->where('status', 1)->count(),
                    Trade::whereMonth('created_at', 7)->where('status', 1)->count(),
                    Trade::whereMonth('created_at', 8)->where('status', 1)->count(),
                    Trade::whereMonth('created_at', 9)->where('status', 1)->count(),
                    Trade::whereMonth('created_at', 10)->where('status', 1)->count(),
                    Trade::whereMonth('created_at', 11)->where('status', 1)->count(),
                    Trade::whereMonth('created_at', 12)->where('status', 1)->count(),
                ],
                'pending' => [
                    Trade::whereMonth('created_at', 1)->where('status', 0)->count(),
                    Trade::whereMonth('created_at', 2)->where('status', 0)->count(),
                    Trade::whereMonth('created_at', 3)->where('status', 0)->count(),
                    Trade::whereMonth('created_at', 4)->where('status', 0)->count(),
                    Trade::whereMonth('created_at', 5)->where('status', 0)->count(),
                    Trade::whereMonth('created_at', 6)->where('status', 0)->count(),
                    Trade::whereMonth('created_at', 7)->where('status', 0)->count(),
                    Trade::whereMonth('created_at', 8)->where('status', 0)->count(),
                    Trade::whereMonth('created_at', 9)->where('status', 0)->count(),
                    Trade::whereMonth('created_at', 10)->where('status', 0)->count(),
                    Trade::whereMonth('created_at', 11)->where('status', 0)->count(),
                    Trade::whereMonth('created_at', 12)->where('status', 0)->count(),
                ],
                'cancelled' => [
                    Trade::whereMonth('created_at', 1)->where('status', 2)->count(),
                    Trade::whereMonth('created_at', 2)->where('status', 2)->count(),
                    Trade::whereMonth('created_at', 3)->where('status', 2)->count(),
                    Trade::whereMonth('created_at', 4)->where('status', 2)->count(),
                    Trade::whereMonth('created_at', 5)->where('status', 2)->count(),
                    Trade::whereMonth('created_at', 6)->where('status', 2)->count(),
                    Trade::whereMonth('created_at', 7)->where('status', 2)->count(),
                    Trade::whereMonth('created_at', 8)->where('status', 2)->count(),
                    Trade::whereMonth('created_at', 9)->where('status', 2)->count(),
                    Trade::whereMonth('created_at', 10)->where('status', 2)->count(),
                    Trade::whereMonth('created_at', 11)->where('status', 2)->count(),
                    Trade::whereMonth('created_at', 12)->where('status', 2)->count(),
                ]
            ],
            'activities' => Activity::latest()->take(20),
            'trade_history' => [
                'btc' => Trade::whereHas('coin', function ($q){$q->where('abbr', 'btc');})->where('status', 1)->sum('amount'),
                'etc' => Trade::whereHas('coin', function ($q){$q->where('abbr', 'eth');})->where('status', 1)->sum('amount'),
                'ltc' => Trade::whereHas('coin', function ($q){$q->where('abbr', 'ltc');})->where('status', 1)->sum('amount'),
                'bch' => Trade::whereHas('coin', function ($q){$q->where('abbr', 'bch');})->where('status', 1)->sum('amount'),
                'xrp' => Trade::whereHas('coin', function ($q){$q->where('abbr', 'xrp');})->where('status', 1)->sum('amount'),
            ]
        ];
    }
}
