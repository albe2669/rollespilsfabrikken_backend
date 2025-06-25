<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\Helpers;
use App\Http\Requests\API\Auth\Role\Destroy;
use App\Http\Requests\API\Auth\Role\Index;
use App\Http\Requests\API\Auth\Role\Show;
use App\Http\Requests\API\Auth\Role\Store;
use App\Http\Requests\API\Auth\Role\Update;
use App\Http\Resources\Role\Role as RoleResource;
use App\Http\Resources\Role\RoleCollection;
use App\Http\Resources\Role\RoleWithPermissions;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Index $request)
    {
        if ($request->query('search')) {
            $roles = (new Helpers)->searchItems($request, Role::class, []);
        } else {
            $roles = (new Helpers)->filterItems($request, Role::query());
        }

        return response()->json([
            'message' => 'success',
            'data' => new RoleCollection($roles),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     */
    public function store(Store $request)
    {
        $role = (new Role)->fill($request->validated());
        $role->save();

        return response()->json([
            'message' => 'success',
            'role' => new RoleResource($role->refresh()),
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show(Show $request, Role $role)
    {
        return response()->json([
            'message' => 'success',
            'data' => new RoleWithPermissions($role),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return JsonResponse
     */
    public function update(Update $request, Role $role)
    {
        $role->update($request->validated());

        return response()->json([
            'message' => 'success',
            'role' => new RoleResource($role),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse
     */
    public function destroy(Destroy $request, Role $role)
    {
        $role->delete();

        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
