<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\FileHelpers;
use App\Http\Controllers\Helpers\Helpers;
use App\Http\Requests\API\Post\Destroy;
use App\Http\Requests\API\Post\Index;
use App\Http\Requests\API\Post\Newest;
use App\Http\Requests\API\Post\Show;
use App\Http\Requests\API\Post\Store;
use App\Http\Requests\API\Post\Update;
use App\Http\Resources\Post\Post as PostResource;
use App\Http\Resources\Post\PostIndexCollection;
use App\Http\Resources\Post\PostIndexNewestCollection;
use App\Http\Resources\Post\PostWithUser as PostWithUserResource;
use App\Models\Forum;
use App\Models\Post;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     * Url : /api/forum/{forum}/posts
     *
     * @return JsonResponse
     */
    public function newest(Newest $request, Forum $forum)
    {
        $user = auth()->user();

        $forums = Forum::query();

        if (! $user->isSuperUser()) {
            $forums
                ->whereIn(
                    'obj_id',
                    collect($user->permissions())
                        ->where('level', '>', 1)
                        ->pluck('obj_id'),
                );
        }

        $forums = $forums
            ->select('id')
            ->get();

        $query = Post::query()
            ->whereIn('forum_id', $forums);

        return response()->json([
            'message' => 'success',
            'data' => new PostIndexNewestCollection((new Helpers)->filterItems($request, $query)),
        ], 200);
    }

    /**
     * Display a listing of the resource.
     * Url : /api/forum/{forum}/posts
     *
     * @return JsonResponse
     */
    public function index(Index $request, Forum $forum)
    {

        if ($request->query('search')) {
            $posts = (new Helpers)->searchItems($request, Post::class, [
                [
                    'key' => 'forum_id',
                    'value' => $forum['id'],
                ],
            ]);
        } else {
            $query = $forum
                ->posts()
                ->orderBy('pinned', 'desc')
                ->getQuery();

            $posts = (new Helpers)->filterItems($request, $query);
        }

        return response()->json([
            'message' => 'success',
            'data' => new PostIndexCollection($posts),
        ], 200);
    }

    /**
     * Display the specified resource.
     * Url : /api/forum/{forum}/post/{post}
     *
     * @return JsonResponse
     */
    public function show(Show $request, Forum $forum, Post $post)
    {
        return response()->json([
            'message' => 'success',
            'post' => new PostWithUserResource($post),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     * Url : /api/forum/{forum}/post/
     *
     * @return JsonResponse
     *
     * @throws FileNotFoundException
     */
    public function store(Store $request, Forum $forum)
    {
        $post = new Post;
        $post
            ->fill($request->validated())
            ->user()
            ->associate(auth()->user());

        $forum->posts()->save($post);

        if ($request->hasFile(('files'))) {
            foreach ($request->file('files') as $file) {
                FileHelpers::savePostFile($file, $post->refresh());
            }
        }

        return response()->json([
            'message' => 'success',
            'post' => new PostResource($post->refresh()),
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     * Url : /api/forum/{forum}/post/{post}
     *
     * @return JsonResponse
     */
    public function update(Update $request, Forum $forum, Post $post)
    {
        $post->update($request->validated());

        return response()->json([
            'message' => 'success',
            'post' => new PostResource($post),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     * Url : /api/forum/{forum}/post/{post}
     *
     * @return JsonResponse
     */
    public function destroy(Destroy $request, Forum $forum, Post $post)
    {
        $post->delete();

        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
