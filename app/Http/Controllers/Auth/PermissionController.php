<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers;
use App\Http\Requests\API\Auth\Permission\IndexCalendar;
use App\Http\Requests\API\Auth\Permission\IndexForum;
use App\Http\Resources\Permission\PermissionWithRoles;
use App\Http\Resources\Permission\Permission as PermissionResource;
use App\Http\Resources\Permission\PermissionCollection;
use App\Models\Calendar;
use App\Models\Forum;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\API\Auth\Permission\Index;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Index $request
     * @return JsonResponse
     */
    public function index(Index $request)
    {
        if ($request->query('search')) {
            $permissions = (new Helpers())->searchItems($request, Permission::class, []);
        } else {
            $permissions = (new Helpers())->filterItems($request, Permission::query());
        }

        return response()->json([
            'message' => 'success',
            'data' => new PermissionCollection($permissions),
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Permission $permission
     * @return JsonResponse
     */
    public function show(Permission $permission)
    {
        return response()->json([
            'message' => 'success',
            'data' => new PermissionWithRoles($permission),
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexCalendar $request
     * @param Calendar $calendar
     * @return JsonResponse
     */
    public function calendarIndex(IndexCalendar $request, Calendar $calendar) {
        return response()->json([
            'message' => 'success',
            'data' => PermissionResource::collection($calendar->obj->permissions),
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexForum $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function forumIndex(IndexForum $request, Forum $forum) {
        return response()->json([
            'message' => 'success',
            'data' => PermissionResource::collection($forum->obj->permissions),
        ], 200);
    }
}
