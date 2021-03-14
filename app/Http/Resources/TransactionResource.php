<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'wallet' => new WalletResource($this->wallet),
            'type' => $this['type'],
            'via' => $this['via'],
            'amount' => $this['amount'],
            'status' => $this->getStatus($this['status']),
        ];
    }

    protected function getStatus($key): ?string
    {
        $status = null;
        switch ($key) {
            case 0:
                $status = 'pending';
                break;
            case 1:
                $status = 'success';
                break;
            case 2:
                $status = 'cancelled';
                break;
        }
        return $status;
    }
}
