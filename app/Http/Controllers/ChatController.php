<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Trade;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Trade $trade): \Illuminate\Http\JsonResponse
    {
        return response()->json(['data' => $trade->chats()->get()]);
    }

    public function store(Request $request)
    {

    }
}
