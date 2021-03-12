<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this['id'],
            'code' => $this['code'],
            'first_name' => $this['first_name'],
            'last_name' => $this['last_name'],
            'other_name' => $this['other_name'],
            'username' => $this['username'],
            'email' => $this['email'],
            'phone' => $this['phone'],
            'status' => $this['active'] ? 'Active' : 'Inactive'
        ];
    }
}
