<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\Constants\EventConstants;
use App\Http\Controllers\Helpers\EventHelpers;
use App\Http\Requests\API\Event\All;
use App\Http\Requests\API\Event\Destroy;
use App\Http\Requests\API\Event\Index;
use App\Http\Requests\API\Event\Show;
use App\Http\Requests\API\Event\Store;
use App\Http\Requests\API\Event\Update;
use App\Http\Resources\Event\Event as EventJsonResource;
use App\Http\Resources\Event\EventCensored;
use App\Http\Resources\Event\EventWithUser;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\EventMeta;
use App\Models\EventSerie;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     * Url : /api/forum/{forum}/posts
     *
     * @return JsonResponse
     */
    public function all(All $request, Calendar $calendar)
    {
        $user = auth()->user();

        $calendars = EventHelpers::getCalendars(auth()->user());

        [$startDate, $endDate] = EventHelpers::parseRequest($request);

        return response()->json([
            'message' => 'success',
            'data' => EventWithUser::collection(
                EventHelpers::getEventsInRange($startDate, $endDate, $calendars, [])
                    ->where('start_timestamp', '>=', $startDate->timestamp)
                    ->where('end_timestamp', '<=', $endDate->timestamp),
            ),
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Index $request, Calendar $calendar)
    {
        [$startDate, $endDate] = EventHelpers::parseRequest($request);

        $totalEvents = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $timestamp = $date->startOfDay()->timestamp;

            $events = EventHelpers::getEventQuery($calendar->events()->getQuery(), true, $timestamp)
                ->get()
                ->each(function ($event, $item) use ($timestamp) {
                    EventHelpers::convertEvent($event, $timestamp);
                });

            $oneTime = EventHelpers::getEventQuery($calendar->events()->getQuery(), false, $timestamp)
                ->get()
                ->each(function ($event, $item) use ($timestamp) {
                    EventHelpers::convertEvent($event, $timestamp);
                });

            $totalEvents[] = $events->merge($oneTime);
        }

        return response()->json([
            'message' => 'success',
            'data' => EventWithUser::collection(
                collect($totalEvents)
                    ->flatten()
                    ->where('start_timestamp', '>=', $startDate->timestamp)
                    ->where('end_timestamp', '<=', $endDate->timestamp),
            ),
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show(Show $request, Calendar $calendar, Event $event)
    {
        // Parse the date from the request
        $date = Carbon::parse($request['date']);

        $event = EventHelpers::checkForEventInstance($event, $date->copy()->startOfDay()->timestamp);

        if ($event == false) {
            return response()->json([
                'message' => 'There is no instance of this event on that date',
            ], 404);
        }

        return response()->json([
            'message' => 'success',
            'event' => new EventWithUser($event),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     */
    public function store(Store $request, Calendar $calendar)
    {
        // Parse the request data
        $data = $request->validated();

        [
            $start,
            $end,
            $data,
            $metaData,
            $resources,
            $warnings,
        ] = EventHelpers::parseData($data, $calendar);

        // Make sure the start is not after end date
        if ($start->isAfter($end)) {
            return response()->json([
                'message' => 'An events start date cant be after its end',
            ], 401);
        }

        if ($metaData['repeat_end'] > Carbon::createFromTimestamp($data['start_timestamp'])->addYears(2)->timestamp) {
            $metaData['repeat_end'] = Carbon::createFromTimestamp($data['start_timestamp'])->addYears(2)->timestamp;
        }

        // Forcefully set the event length
        if ($data['event_length'] > $metaData['repeat_interval'] && $metaData['repeat_interval'] != 0) {
            $data['event_length'] = $metaData['repeat_interval'];
        }

        // Create the new event
        $event = (new Event)
            ->fill($data);

        // Associate with the creating user
        $event
            ->user()
            ->associate(auth()->user());

        // Create a new series and associate with the event
        $series = (new EventSerie)->create();
        $event->series()->associate($series);

        // Save the event
        $calendar->events()->save($event);

        // Fill the event meta data
        $eventMeta = (new EventMeta)->fill($metaData);

        // Save and associate the metadata with the event
        $event->refresh()->meta()->save($eventMeta);

        // Save the event resources
        EventHelpers::saveEventResources($resources, $event);

        $response = [
            'message' => 'success',
            'event' => new EventJsonResource(
                EventHelpers::convertEvent(
                    collect($event)
                        ->merge($eventMeta->refresh())
                        ->toArray(),
                    $start->timestamp,
                ),
            ),
        ];

        if (count($warnings) > 0) {
            $response['warnings'] = $warnings;
        }

        return response()->json($response, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return JsonResponse
     */
    public function update(Update $request, Calendar $calendar, Event $event)
    {
        // Retrieve the data
        $data = $request->validated();

        [
            $start,
            $end,
            $data,
            $metaData,
            $resources,
            $warnings,
        ] = EventHelpers::parseData($data, $calendar);

        // Make sure the start is not after end date
        if ($start->isAfter($end)) {
            return response()->json([
                'message' => 'An events start date cant be after its end',
            ], 401);
        }

        // Create a copy of the original data
        $originalEventMeta = $event->meta;
        $originalEvent = $event;

        // Standalone events dont have a series or anything special to them
        // Therefore if updated they can just be completely updated
        if ($originalEventMeta['repeat_interval'] == 0) {
            $event->update($data);
            $originalEventMeta->update($metaData);

            EventHelpers::updateEventResources($event, $resources);

            // Update the series
        } elseif (isset($data['recurrence']['series']) && ($data['recurrence']['series'] === true)) {
            // Get all the events in the series
            $series = $event->series;

            // Loop them
            foreach ($series->events as $event) {
                // Update the event
                $event->update($data);

                // Get the metadata for the current event
                $meta = $event->meta;

                // Set the repeat interval
                $meta->repeat_interval = EventConstants::$recurrenceIntervalLookup[$data['recurrence']['type']];

                // Save the metadata
                $meta->save();

                // Update resources
                EventHelpers::updateEventResources($event, $resources);
            }

        } elseif (isset($data['recurrence']['apply_to_all']) && ($data['recurrence']['apply_to_all'] === true)) {
            // Update the original event
            $event->update($data);

            // Update the recurrence of the current branch of the series
            $originalEventMeta->update($metaData);
        } elseif (isset($data['recurrence']['only_this']) && ($data['recurrence']['only_this'] === true)) {
            // Check if there is an event instance on the given date
            $event = EventHelpers::checkForEventInstance($event, $start->copy()->startOfDay()->timestamp);

            if ($event == false) {
                return response()->json([
                    'message' => 'There is no instance of this event on that date',
                ], 404);
            }

            // Set the now first subseres end date, to the current events start date
            $originalEventMeta['repeat_end'] = $start->startOfDay()->timestamp;
            $originalEventMeta->save();

            // Create a new series, that is after the current event, and contains the same repetition elements
            $eventData = [
                'repeat_start' => $start->copy()->startOfDay()->timestamp + $originalEventMeta['repeat_interval'],
                'repeat_interval' => $originalEventMeta['repeat_interval'],
                'repeat_end' => $originalEventMeta['repeat_end'],
            ];

            // Fill in the data
            $meta = (new EventMeta)->fill($eventData);

            // Create the data
            $event = (new Event)->fill([
                'title' => $event['title'],
                'description' => $event['description'],
                'start' => Carbon::createFromTimestamp($event['start_timestamp'])->toTimeString(),
                'event_length' => $event['event_length'],
            ]);

            // Associate
            $event->user()->associate($originalEvent->user);
            $event->series()->associate($originalEvent->series);

            // Save the events changes and meta
            $calendar->events()->save($event);
            $event->refresh()->meta()->save($meta);

            // Create a new standalone event
            $eventData = [
                'repeat_start' => $start->copy()->startOfDay()->timestamp,
                'repeat_interval' => 0,
                'repeat_end' => null,
            ];

            // Fill in its meta
            $meta = (new EventMeta)->fill($eventData);

            // Fill in the new events data
            $event = (new Event)->fill($data);

            // Associate
            $event->user()->associate($originalEvent->user);
            $event->series()->associate($originalEvent->series);

            // Save the event and its meta
            $calendar->events()->save($event);
            $event->refresh()->meta()->save($meta);

            // Set the new events resources to be the ones given in the request
            EventHelpers::saveEventResources($resources, $event);
        } else {
            // This will split the recurrence series into 2.
            // It will end the original and create a new one.
            // Create a new event, aka the split
            $event = (new Event)
                ->fill($data);

            // Assign it to the user
            $event
                ->user()
                ->associate(auth()->user());

            // Assign the new event to the series of the first event
            $event
                ->series()
                ->associate($originalEvent->series);

            // Save it
            $calendar->events()->save($event);

            // Set an end date to the original recurrence series
            $originalEventMeta['repeat_end'] = $start->startOfDay()->timestamp;
            $originalEventMeta->save();

            // Create a new recurrence series and save it to the new event
            $eventMeta = (new EventMeta)->fill($metaData);
            $event->refresh()->meta()->save($eventMeta);

            // Set the new events resources to be the ones given in the new event
            EventHelpers::saveEventResources($resources, $event);
        }

        // Set the event data such that it is ready for showing
        $event['type'] = EventConstants::$recurrenceStringLookup[$metaData['repeat_interval']];
        $event['start'] = $start->format('Y-m-d\TH:i:s.v\Z');
        $event['end'] = $end->format('Y-m-d\TH:i:s.v\Z');

        $response = [
            'message' => 'success',
            'event' => new EventJsonResource(collect($event)->merge($event->meta)->toArray()),
        ];

        if (count($warnings) > 0) {
            $response['warnings'] = $warnings;
        }

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse
     */
    public function destroy(Destroy $request, Calendar $calendar, Event $event)
    {
        // Retrieve the data
        $data = $request->validated();

        // Create a copy of the original data
        $meta = $event->meta;

        if ($meta['repeat_interval'] != 0) {
            $date = Carbon::parse($data['date']);
        }

        // Standalone events dont have a series or anything special to them
        // Therefore they can just be deleted
        if ($meta['repeat_interval'] == 0) {
            $event->delete();
            $event->meta->delete();

            // Update the series
        } elseif (isset($data['series']) && ($data['series'] === true)) {
            $series = $event->series;
            $events = $series->events;

            $events->each(function (Event $event, $item) {
                $event->meta->delete();
                $event->delete();
            });

            $series->delete();
        } elseif (isset($data['apply_to_all']) && ($data['apply_to_all'] === true)) {
            $event->delete();
            $event->meta->delete();
        } elseif (isset($data['only_this']) && ($data['only_this'] === true)) {
            // Check if there is an event instance on the given date
            $event = EventHelpers::checkForEventInstance($event, $date->copy()->startOfDay()->timestamp);

            if ($event == false) {
                return response()->json([
                    'message' => 'There is no instance of this event on that date',
                ], 404);
            }

            // Define the new event meta data
            $metaData = [
                'repeat_start' => $date->startOfDay()->timestamp + $meta['repeat_interval'],
                'repeat_interval' => $meta['repeat_interval'],
                'repeat_end' => $meta['repeat_end'],
            ];

            $meta['repeat_end'] = $date->startOfDay()->timestamp;
            $meta->save();

            // Create a new meta
            $meta = (new EventMeta)->fill($metaData);

            // Create and save a new event that starts right after the one just deleted
            $newEvent = (new Event)->fill($event->only(['title', 'description', 'start', 'event_length']));
            $newEvent->user()->associate($event->user);
            $newEvent->series()->associate($event->series);

            $calendar->events()->save($newEvent);
            $newEvent->refresh()->meta()->save($meta);
        } else {
            // Check if there is an event instance on the given date
            $event = EventHelpers::checkForEventInstance($event, $date->copy()->startOfDay()->timestamp);

            if ($event == false) {
                return response()->json([
                    'message' => 'There is no instance of this event on that date',
                ], 404);
            }

            $meta['repeat_end'] = $date->startOfDay()->timestamp;
            $meta->save();
        }

        return response()->json([
            'message' => 'success',
        ], 200);
    }

    public function check(Store $request, Calendar $calendar)
    {
        // Parse the request data
        $data = $request->validated();
        [
            $start,
            $end,
            $data,
            $metaData,
            $resources,
            $warnings,
        ] = EventHelpers::parseData($data, $calendar);

        $errors = [];

        // Make sure the start is not after end date
        if ($start->isAfter($end)) {
            $errors[] = [
                'message' => 'An events start date cant be after its end',
            ];
        }

        if ($metaData['repeat_end'] > Carbon::createFromTimestamp($data['start_timestamp'])->addYears(2)->timestamp) {
            $errors[] = [
                'message' => 'An event cant repeat for more than 2 years',
            ];
        }

        if ($data['event_length'] > $metaData['repeat_interval'] && $metaData['repeat_interval'] != 0) {
            $errors[] = [
                'message' => 'An event cant be longer than its recurring interval',
            ];
        }

        $ignoredEvents = [];

        if ($request->query('ignore')) {
            foreach (explode(',', $request->query('ignore')) as $item) {
                $ignoredEvent = (new Event)->whereUuid($item)->select('id')->first();

                if ($ignoredEvent != null) {
                    $ignoredEvents[] = $ignoredEvent;
                }
            }
        }

        $events = EventHelpers::getEventsInRange($start, $end, [$calendar->id], $ignoredEvents);

        foreach ($events as $event) {
            // Determine if the events overlap by checking if the events do not overlap
            if (! ($event['end_timestamp'] <= $start->timestamp || $event['start_timestamp'] >= $end->timestamp)) {
                $warnings[] = [
                    'message' => 'Event overlaps with another event',
                    'event' => new EventWithUser($event),
                ];
            }
        }

        if ($calendar->allowed_resource != 'none') {
            $calendars = EventHelpers::getCalendars(auth()->user());

            foreach ($resources as $resource) {
                $events = (EventHelpers::getResourceEventsInRange($start, $end, $resource, $ignoredEvents));

                if (count($events) > 0) {
                    $warning = [
                        'message' => 'This resource is already booked',
                        'resource' => new \App\Http\Resources\Resource\Resource($resource),
                        'bookings' => [],
                    ];

                    foreach ($events as $event) {
                        if ($calendars->contains($event->calendar->id)) {
                            $warning['bookings'][] = new EventWithUser($event);
                        } else {
                            $warning['bookings'][] = new EventCensored($event);
                        }
                    }

                    $warnings[] = $warning;
                }
            }

        }

        if (count($warnings) > 0 || count($errors) > 0) {
            return response()->json([
                'message' => 'There were errors/warnings',
                'warnings' => $warnings,
                'errors' => $errors,
            ], 400);
        } else {
            return response()->json([
                'message' => 'There were no errors/warnings',
            ], 200);
        }
    }
}
