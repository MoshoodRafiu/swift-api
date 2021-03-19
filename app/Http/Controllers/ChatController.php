<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function index(Trade $trade): \Illuminate\Http\JsonResponse
    {
        return response()->json(['data' => $trade->chats()->get()]);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "trade_id" => ['required'],
            "message" => ['required'],
            "type" => ['required', 'string']
        ]);
        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 422);
        }
        $trade = Trade::find($request['trade_id']);
        if (!$trade){
            return response()->json(['error' => 'Trade not found'], 400);
        }
        if ($request['type'] == 'text'){
            $msg = $trade->chats()->create([
                'user_id' => auth()->user()['id'],
                'message' => $request['message'],
                'type' => $request['type']
            ]);
        }elseif ($request['type'] == 'file'){
            $path = 'proofs';
            $transferDoc = auth()->user()['code'].'-'. time() . '.' . $request['file']->getClientOriginalExtension();
            $request['file']->move($path, $transferDoc);
            $msg = $trade->chats()->create([
                'user_id' => auth()->user()['id'],
                'message' => $path."/".$transferDoc,
                'type' => $request['type']
            ]);
        }else{
            return response()->json(['error' => 'Message type not allowed'], 400);
        }
        return response()->json([
            'message' => 'Message sent',
            'data' => $msg
        ]);
    }
}
