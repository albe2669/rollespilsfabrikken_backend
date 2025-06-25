<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Helpers\Constants\EventConstants;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\EventResource;
use App\Models\Resource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EventHelpers
{
    public static function convertStandardToCarbon($date)
    {
        return Carbon::createFromFormat('d-m-Y H:i:s', $date);
    }

    public static function convertEvent($event, $timestamp)
    {
        $start = self::convertStandardToCarbon(Carbon::createFromTimestamp($timestamp)->format('d-m-Y').' '.$event['start']);
        $end = $start->copy()->addSeconds($event['event_length']);

        $event['start_timestamp'] = $start->timestamp;
        $event['end_timestamp'] = $end->timestamp;

        $event['start'] = $start->format('Y-m-d\TH:i:s.v\Z');
        $event['end'] = $end->format('Y-m-d\TH:i:s.v\Z');

        $event['type'] = EventConstants::$recurrenceStringLookup[$event['repeat_interval']];

        return $event;
    }

    public static function checkForEventInstance(Event $event, $timestamp)
    {
        $event = self::convertEvent(collect($event)->merge($event->meta)->toArray(), $timestamp);

        // Check if date is before the repetition start or if the date is after the event end
        if ($event['repeat_start'] > $timestamp || $event['end_timestamp'] < $timestamp) {
            return false;
        }

        // Check if date is after repetition end
        if ($event['repeat_end'] != null) {
            $end = Carbon::createFromTimestamp($event['repeat_end']);

            if ($end->timestamp < $timestamp) {
                return false;
            }
        }

        // Perform and validate the repeating calculation
        if ($event['repeat_interval'] != 0 && (($timestamp - $event['repeat_start']) % $event['repeat_interval']) != 0) {
            return false;
        }

        return $event;
    }

    public static function getEventQuery(Builder $query, bool $recurring, int $timestamp): Builder
    {
        $query->rightJoin('event_metas', 'event_metas.event_id', '=', 'events.id');

        if ($recurring) {
            $query
                ->where('repeat_start', '<=', $timestamp)
                ->where(function ($query) use ($timestamp) {
                    return $query->whereNull('repeat_end')
                        ->orWhere('repeat_end', '>', $timestamp);
                })
                ->whereRaw('(? - cast(repeat_start as signed)) % repeat_interval = 0', $timestamp);
        } else {
            $query
                ->where('repeat_start', '=', $timestamp)
                ->where('repeat_interval', '=', 0);
        }

        return $query;
    }

    public static function parseRequest($request)
    {
        if (! $request->query('start') && ! $request->query('end')) {
            $startDate = Carbon::now();
            $endDate = Carbon::now()->addDays(7);
        } else {
            $startDate = Carbon::parse($request->query('start'));
            $endDate = Carbon::parse($request->query('end'));
        }

        if (! $startDate || ! $endDate) {
            return response()->json([
                'message' => 'That start or/and end does not fit the approved format',
            ]);
        }

        return [$startDate, $endDate];
    }

    public static function parseData($data, $calendar)
    {
        $warnings = [];

        // Parse start date
        $start = Carbon::parse($data['start']);
        $end = Carbon::parse($data['end']);

        $data['start_timestamp'] = $start->timestamp;
        $data['end_timestamp'] = $end->timestamp;

        // Set the start time, without date, and define the event length in seconds
        $data['start'] = $start->copy()->toTimeString();
        $data['event_length'] = $start->diffInSeconds($end);

        // Parse the recurrence data
        $metaData = [
            'repeat_start' => $start->copy()->startOfDay()->timestamp,
            'repeat_interval' => 0,
            'repeat_end' => null,
        ];

        // Set the repeat interval
        if ($data['recurring']) {
            $metaData['repeat_interval'] = EventConstants::$recurrenceIntervalLookup[$data['recurrence']['type']];
        }

        // Set the optional end of recurrence
        if (isset($data['recurrence']['end'])) {
            $metaData['repeat_end'] = Carbon::parse($data['recurrence']['end'])->addDay()->startOfDay()->timestamp;
        }

        // Locate the resources if any are set
        $resources = collect();
        if (isset($data['resources'])) {
            // Remove any duplicates from the array, just in case
            $data['resources'] = array_unique($data['resources']);

            foreach ($data['resources'] as $resource) {
                $retrievedResource = Resource::whereUuid($resource)->firstOrFail();

                if ($retrievedResource->type == 'room' && ! $calendar->canUseRooms()) {
                    $warnings[] = [
                        'message' => 'Rooms are not allowed in this calendar',
                        'room' => new \App\Http\Resources\Resource\Resource($retrievedResource),
                    ];

                    continue;
                }

                if ($retrievedResource->type == 'equipment' && ! $calendar->canUseEquipment()) {
                    $warnings[] = [
                        'message' => 'Equipment is not allowed in this calendar',
                        'equipment' => new \App\Http\Resources\Resource\Resource($retrievedResource),
                    ];

                    continue;
                }

                $resources[] = Resource::whereUuid($resource)->firstOrFail();
            }
        }

        return [$start, $end, $data, $metaData, $resources->keyBy('id'), $warnings];
    }

    public static function getCalendars(User $user)
    {
        $calendars = Calendar::query();

        // Get calendars the user has access to
        if (! $user->isSuperUser()) {
            $calendars = $calendars
                ->whereIn(
                    'obj_id',
                    collect($user->permissions())
                        ->where('level', '>', 1)
                        ->pluck('obj_id'),
                );
        }

        return $calendars
            ->select('id')
            ->get();
    }

    public static function getEventsInRange($start, $end, $calendars, $ignoredEvents)
    {
        $events = [];

        $baseQuery = Event::query();
        foreach ($ignoredEvents as $ignoredEvent) {
            $baseQuery
                ->where('events.id', '!=', $ignoredEvent->id);
        }

        for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
            $timestamp = $date->startOfDay()->timestamp;

            $recurring = self::getEventQuery(clone $baseQuery, true, $timestamp)
                ->whereIn('calendar_id', $calendars)
                ->get()
                ->each(function ($event, $item) use ($timestamp) {
                    self::convertEvent($event, $timestamp);
                });

            $oneTime = self::getEventQuery(clone $baseQuery, false, $timestamp)
                ->whereIn('calendar_id', $calendars)
                ->get()
                ->each(function ($event, $item) use ($timestamp) {
                    self::convertEvent($event, $timestamp);
                });

            $events[] = $recurring->merge($oneTime);
        }

        return collect($events)->flatten();
    }

    public static function getResourceEventsInRange($start, $end, Resource $resource, $ignoredEvents)
    {
        $events = [];

        $baseQuery = $resource->events()->getQuery();
        foreach ($ignoredEvents as $ignoredEvent) {
            $baseQuery
                ->where('events.id', '!=', $ignoredEvent->id);
        }

        for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
            $timestamp = $date->startOfDay()->timestamp;

            $recurring = self::getEventQuery(clone $baseQuery, true, $timestamp)
                ->get()
                ->each(function ($event, $item) use ($timestamp) {
                    self::convertEvent($event, $timestamp);
                });

            $oneTime = self::getEventQuery(clone $baseQuery, false, $timestamp)
                ->get()
                ->each(function ($event, $item) use ($timestamp) {
                    self::convertEvent($event, $timestamp);
                });

            $events[] = $recurring->merge($oneTime);
        }

        return collect($events)->flatten();
    }

    public static function filterEventResources(Collection $oldResources, Collection $newResources)
    {
        foreach ($oldResources as $oldResource) {
            if ($newResources->contains($oldResource)) {
                $newResources->forget($oldResource->id);
            } else {
                $oldResource->delete();
            }
        }

        return $newResources;
    }

    public static function saveEventResources($resources, $event)
    {
        $calendar = $event->calendar;

        foreach ($resources as $resource) {
            $eventResource = new EventResource;

            $eventResource->resource()->associate($resource);
            $eventResource->event()->associate($event);

            $eventResource->save();
        }
    }

    public static function updateEventResources($event, $resources)
    {
        $originalEventResources = EventResource::where('event_id', '=', $event->id)->get();
        $resources = EventHelpers::filterEventResources($originalEventResources, $resources);
        EventHelpers::saveEventResources($resources, $event);
    }
}
