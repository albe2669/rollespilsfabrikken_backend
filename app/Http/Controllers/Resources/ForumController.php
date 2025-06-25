<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\Helpers;
use App\Http\Requests\API\Forum\Destroy;
use App\Http\Requests\API\Forum\Index;
use App\Http\Requests\API\Forum\Show;
use App\Http\Requests\API\Forum\Store;
use App\Http\Requests\API\Forum\Update;
use App\Http\Resources\Forum\Forum as ForumResource;
use App\Http\Resources\Forum\ForumCollection;
use App\Http\Resources\Forum\ForumWithPosts as ForumWithPostsResource;
use App\Models\Forum;
use App\Models\Obj;
use App\Policies\PolicyHelper;
use Illuminate\Http\JsonResponse;

// Models

// Helpers

// Requests

class ForumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Index $request)
    {
        $user = auth()->user();

        $forums = Forum::query();

        if (! $user->isSuperUser()) {
            $forums = $forums
                ->whereIn(
                    'obj_id',
                    collect($user->permissions())
                        ->where('level', '>', 1)
                        ->pluck('obj_id'),
                );
        }

        $forums = (new Helpers)->filterItems($request, $forums);

        return response()->json([
            'message' => 'success',
            'data' => new ForumCollection($forums),
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show(Show $request, Forum $forum)
    {
        $forum['access_level'] = (new PolicyHelper)->getLevel(auth()->user(), $forum['obj_id']);

        return response()->json([
            'message' => 'success',
            'forum' => new ForumWithPostsResource($forum),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     */
    public function store(Store $request)
    {
        $forum = (new Forum)
            ->fill($request->validated())
            ->obj()
            ->associate(
                (new Obj)->create([
                    'type' => 'forum',
                ]),
            );
        $forum->save();

        return response()->json([
            'message' => 'success',
            'forum' => new ForumResource($forum->refresh()),
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return JsonResponse
     */
    public function update(Update $request, Forum $forum)
    {
        $forum->update($request->validated());

        return response()->json([
            'message' => 'success',
            'forum' => new ForumResource($forum),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse
     */
    public function destroy(Destroy $request, Forum $forum)
    {
        $forum = $forum->delete();

        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
