<?php

namespace App\Http\Resources\Event;

use App\Http\Resources\User\User as UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Event extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $end = Carbon::createFromTimestamp($this['repeat_end'])->addDays(-1)->toISOString();

        return [
            'id' => $this['uuid'],
            'title' => $this['title'],
            'description' => $this['description'],
            'start' => $this['start'],
            'end' => $this['end'],
            'recurrence' => [
                'start' => Carbon::createFromTimestamp($this['repeat_start']),
                'end' => $end != "1969-12-31T00:00:00.000000Z" ? $end : null,
                'type' => $this['type'],
            ],
            'permissions' => [
                'can_update' => auth()->user()->can('update', (new \App\Models\Event)->find($this['id'])),
                'can_delete' => auth()->user()->can('delete', (new \App\Models\Event)->find($this['id']))
            ],
            'updated_at' => $this['updated_at'],
            'created_at' => $this['created_at'],
        ];
    }
}
