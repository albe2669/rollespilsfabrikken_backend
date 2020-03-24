<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

// Models
use App\Models\User;
use App\Models\Forum;
use App\Models\Obj;

// Helpers
use App\Policies\PolicyHelper;
use App\Http\Controllers\Helpers;

// Requests
use App\Http\Requests\API\Forum\Index;
use App\Http\Requests\API\Forum\Store;
use App\Http\Requests\API\Forum\Update;
use App\Http\Requests\API\Forum\Destroy;
use App\Http\Requests\API\Forum\Show;

class ForumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Index $request
     * @return JsonResponse
     */
    public function index(Index $request)
    {
        $user = auth()->user();

        $forums = Forum::query();
        if (!$user->isSuperUser()) {
            $forums = $forums
                ->whereIn('obj_id', collect($user->permissions())
                    ->where('level', '>', 1)
                    ->pluck('obj_id')
                );
        }

        $forums = (new Helpers())->filterItems($request, $forums);

        return response()->json([
            'message' => 'success',
            'forums' => $forums
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Show $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function show(Show $request, Forum $forum)
    {
        $forum['access_level'] = (new PolicyHelper())->getLevel(auth()->user(), $forum['obj_id']);

        $forum['posts'] = $forum
            ->posts()
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'success',
            'forum' => $forum,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Store $request
     * @return JsonResponse
     */
    public function store(Store $request)
    {
        $obj = (new Obj)->create([
            'type' => 'forum'
        ]);

        $data = $request->validated();
        $data['obj_id'] = $obj['id'];
        $forum = (new Forum)->create($data);

        return response()->json([
            'message' => 'success',
            'forum' => $forum
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Update $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function update(Update $request, Forum $forum)
    {
        $data = $request->validated();
        $forum->update($data);

        return response()->json([
            'message' => 'success',
            'forum' => $forum
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Destroy $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function destroy(Destroy $request, Forum $forum)
    {
        $forum = $forum->delete();

        return response()->json([
            'message' => 'success'
        ], 200);
    }
}
