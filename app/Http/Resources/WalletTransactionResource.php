<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this['id'],
            'coin' => new CoinResource($this->coin),
            'address' => $this['address'],
            'balance' => $this['balance'],
            'transaction' => $this->transactions
        ];
    }
}
