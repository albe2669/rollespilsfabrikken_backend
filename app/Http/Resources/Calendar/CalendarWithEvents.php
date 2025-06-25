<?php

namespace App\Http\Resources\Calendar;

use App\Models\Event;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarWithEvents extends JsonResource
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
            'id' => $this->uuid,
            'name' => $this->title,
            'description' => $this->description,
            'colour' => $this->colour,
            'resources' => [
                'rooms_allowed' => $this->canUseRooms(),
                'equipment_allowed' => $this->canUseEquipment(),
            ],
            'events' => $this->events()->count(),
            'permissions' => [
                'can_update' => auth()->user()->can('update', $this->resource),
                'can_delete' => auth()->user()->can('delete', $this->resource),
                'can_add_events' => auth()->user()->can('create', [Event::class, $this->resource]),
            ],
        ];
    }
}
