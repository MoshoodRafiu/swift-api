<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TradeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this['id'],
            'coin' => new CoinResource($this->coin),
            'buyer' => new AuthResource($this->buyer),
            'seller' => new AuthResource($this->seller),
            'agent' => new AuthResource($this->agent),
            'amount' => $this['amount'],
            'amount_usd' => $this['amount_usd'],
            'amount_ngn' => $this['amount_ngn'],
            'buyer_has_summoned' => $this['buyer_has_summoned'] == 1,
            'seller_has_summoned' => $this['seller_has_summoned'] == 1,
            'buyer_status' => $this['buyer_status'],
            'seller_status' => $this['seller_status'],
            'duration' => $this['amount_ngn'],
            'payment' => new PaymentResource($this->payment),
            'seller_rating' => $this->ratings()->where('user_id', $this['seller_id'])->first()['star'],
            'buyer_rating' => $this->ratings()->where('user_id', $this['buyer_id'])->first()['star'],
        ];
    }
}
