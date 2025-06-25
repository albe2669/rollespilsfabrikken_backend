<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Resource\Destroy;
use App\Http\Requests\API\Resource\Index;
// Resources
use App\Http\Requests\API\Resource\Show;
// Requests
use App\Http\Requests\API\Resource\Store;
use App\Http\Requests\API\Resource\Update;
use App\Http\Resources\Resource\Resource as ResourceResource;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;

class ResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Index $request)
    {
        return response()->json([
            'message' => 'success',
            'resources' => ResourceResource::collection((new Resource)->paginate()),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     */
    public function store(Store $request)
    {
        return response()->json([
            'message' => 'success',
            'resource' => new ResourceResource(
                (new Resource)->create($request->validated()),
            ),
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show(Show $request, Resource $resource)
    {
        return response()->json([
            'message' => 'success',
            'resource' => new ResourceResource($resource),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return JsonResponse
     */
    public function update(Update $request, Resource $resource)
    {
        $resource->update($request->validated());

        return response()->json([
            'message' => 'success',
            'resource' => new ResourceResource($resource->refresh()),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse
     */
    public function destroy(Destroy $destroy, Resource $resource)
    {
        $resource->delete();

        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
