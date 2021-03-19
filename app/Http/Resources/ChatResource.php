<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
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
            'sender' => new AuthResource($this->user),
            'message' => $this['type'] == 'text' ? $this['message'] : url($this['message']),
            'type' => $this['type'],
            'sent' => $this['created_at']->diffForHumans(),
            'is_agent' => $this['user_id'] == $this->trade['agent_id']
        ];
    }
}
