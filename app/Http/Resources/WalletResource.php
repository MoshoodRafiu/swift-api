<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
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
            'address' => AddressResource::collection($this->addresses),
            'balance' => $this['balance'],
        ];
    }
}
