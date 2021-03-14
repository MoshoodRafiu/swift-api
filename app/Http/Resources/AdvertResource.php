<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdvertResource extends JsonResource
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
            'user' => new AuthResource($this->user),
            'coin' => new CoinResource($this->coin),
            'type' => $this['type'],
            'min' => $this['min'],
            'max' => $this['max'],
            'rate' => $this['rate'],
            'duration' => $this['duration'],
            'status' => $this['active'] ? 'Active' : 'Disabled',
        ];
    }
}
