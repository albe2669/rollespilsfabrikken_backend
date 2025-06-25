<?php

namespace App\Http\Resources\Event;

use App\Http\Resources\Calendar\CalendarWithoutDelete;
use App\Http\Resources\Resource\Resource as ResourceResource;
use App\Http\Resources\User\User as UserResource;
use App\Models\Calendar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EventWithUser extends JsonResource
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
            'title' => $this['title'],
            'description' => isset($this['description']) ? $this['description'] : '',
            'start' => $this['start'],
            'end' => $this['end'],
            'resources' => ResourceResource::collection($event->resources),
            'parent' => new CalendarWithoutDelete(Calendar::find($this['calendar_id'])),
            'recurrence' => [
                'start' => Carbon::createFromTimestamp($this['repeat_start'])->format('Y-m-d\TH:i:s.v\Z'),
                'end' => $end != '1969-12-31T00:00:00.000Z' ? $end : null,
                'type' => $this['type'],
            ],
            'user' => new UserResource((new User)->find($this['user_id'])),
            'permissions' => [
                'can_update' => auth()->user()->can('update', $event),
                'can_delete' => auth()->user()->can('delete', $event),
            ],
            'updated_at' => Carbon::parse($this['updated_at'])->format('Y-m-d\TH:i:s.v\Z'),
            'created_at' => Carbon::parse($this['created_at'])->format('Y-m-d\TH:i:s.v\Z'),
        ];
    }
}
