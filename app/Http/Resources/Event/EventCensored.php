<?php

namespace App\Http\Resources\Event;

use App\Http\Resources\User\User as UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EventCensored extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $event = (new \App\Models\Event)->find($this['id']);
        $end = Carbon::createFromTimestamp($this['repeat_end'])->addDays(-1)->format('Y-m-d\TH:i:s.v\Z');

        return [
            'id' => $this['uuid'],
            'start' => $this['start'],
            'end' => $this['end'],
            'user' => new UserResource((new User)->find($this['user_id'])),
            'created_at' => Carbon::parse($this['created_at'])->format('Y-m-d\TH:i:s.v\Z'),
        ];
    }
}
