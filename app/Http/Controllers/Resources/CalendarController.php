<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\Helpers;
use App\Http\Requests\API\Calendar\Destroy;
use App\Http\Requests\API\Calendar\Index;
use App\Http\Requests\API\Calendar\Show;
use App\Http\Requests\API\Calendar\Store;
use App\Http\Requests\API\Calendar\Update;
use App\Http\Resources\Calendar\Calendar as CalendarResource;
use App\Http\Resources\Calendar\CalendarCollection;
use App\Http\Resources\Calendar\CalendarWithEvents;
use App\Models\Calendar;
use App\Models\Obj;
use App\Policies\PolicyHelper;
use Illuminate\Http\JsonResponse;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Index $request)
    {
        $user = auth()->user();

        $calendars = Calendar::query();

        if (! $user->isSuperUser()) {
            $calendars = $calendars
                ->whereIn(
                    'obj_id',
                    collect($user->permissions())
                        ->where('level', '>', 1)
                        ->pluck('obj_id'),
                );
        }

        $calendars = (new Helpers)->filterItems($request, $calendars);

        return response()->json([
            'message' => 'success',
            'data' => new CalendarCollection($calendars),
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show(Show $request, Calendar $calendar)
    {
        $calendar['access_level'] = (new PolicyHelper)->getLevel(auth()->user(), $calendar['obj_id']);

        return response()->json([
            'message' => 'success',
            'calendar' => new CalendarWithEvents($calendar),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     */
    public function store(Store $request)
    {
        $data = $request->validated();

        $calendar = (new Calendar)
            ->fill($data)
            ->obj()
            ->associate((new Obj)->create([
                'type' => 'calendar',
            ]));

        if (isset($data['resources']['rooms'])) {
            $rooms = $data['resources']['rooms'];
        } else {
            $rooms = false;
        }

        if (isset($data['resources']['equipment'])) {
            $equipment = $data['resources']['equipment'];
        } else {
            $equipment = false;
        }

        $calendar->setAllowedResources($rooms, $equipment);
        $calendar->save();

        return response()->json([
            'message' => 'success',
            'calendar' => new CalendarResource($calendar->refresh()),
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return JsonResponse
     */
    public function update(Update $request, Calendar $calendar)
    {
        $data = $request->validated();

        if (isset($data['resources']['rooms'])) {
            $rooms = $data['resources']['rooms'];
        } else {
            $rooms = false;
        }

        if (isset($data['resources']['equipment'])) {
            $equipment = $data['resources']['equipment'];
        } else {
            $equipment = false;
        }

        $calendar->setAllowedResources($rooms, $equipment);
        $calendar->update($data);

        return response()->json([
            'message' => 'success',
            'calendar' => new CalendarResource($calendar),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse
     */
    public function destroy(Destroy $request, Calendar $calendar)
    {
        $calendar = $calendar->delete();

        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
