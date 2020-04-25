<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers;
use App\Http\Requests\API\Post\Destroy;
use App\Http\Requests\API\Post\Index;
use App\Http\Requests\API\Post\Pin;
use App\Http\Requests\API\Post\Show;
use App\Http\Requests\API\Post\Store;
use App\Http\Requests\API\Post\Update;
use App\Models\Forum;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

use App\Http\Resources\Post\Post as PostResource;
use App\Http\Resources\Post\PostCollection as PostCollection;
use App\Http\Resources\Post\PostWithUser as PostWithUserResource;
use App\Http\Resources\Post\PostWithUserCollection as PostWithUserCollection;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     * Url : /api/forum/{forum}/posts
     *
     * @param Index $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function index(Index $request,  Forum $forum)
    {
        if ($request->query('search')) {
            $posts = (new Helpers())->searchItems($request, Post::class, [
                [
                    'key' => 'forum_id',
                    'value' => $forum['id']
                ]
            ]);
        } else {
            $query = $forum
                ->posts()
                ->orderBy('pinned', 'desc')
                ->getQuery();

            $posts = (new Helpers())->filterItems($request, $query);
        }

        return response()->json([
            'message' => 'success',
            'data' => new PostWithUserCollection($posts),
        ], 200);
    }

    /**
     * Display the specified resource.
     * Url : /api/forum/{forum}/post/{post}
     *
     * @param Show $request
     * @param Forum $forum
     * @param Post $post
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
     * @param Store $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function store(Store $request, Forum $forum)
    {
        $post = new Post();
        $post
            ->fill($request->validated())
            ->user()
            ->associate(auth()->user());
        $forum->posts()->save($post);

        return response()->json( [
            'message' => 'success',
            'post' => new PostResource($post->refresh())
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     * Url : /api/forum/{forum}/post/{post}
     *
     * @param Update $request
     * @param Forum $forum
     * @param Post $post
     * @return JsonResponse
     */
    public function update(Update $request, Forum $forum, Post $post)
    {
        $post->update($request->validated());

        return response()->json([
            'message' => 'success',
            'post' => new PostResource($post)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     * Url : /api/forum/{forum}/post/{post}
     *
     * @param Destroy $request
     * @param Forum $forum
     * @param Post $post
     * @return JsonResponse
     */
    public function destroy(Destroy $request, Forum $forum, Post $post)
    {
        $post->delete();

        return response()->json([
           'message' => "success"
        ], 200);
    }

    /**
     * Pin the post
     * Url : /api/forum/{forum}/post/{post}/pin
     *
     * @param Pin $request
     * @param Forum $forum
     * @param Post $post
     * @return JsonResponse
     */
    public function pin(Pin $request, Forum $forum, Post $post)
    {
        if ($post->pinned) {
            $post->pinned = false;
        } else {
            $post->pinned = true;
        }

        $post->save();

        return response()->json([
            'message' => 'success',
            'post' => new PostResource($post)
        ], 200);
    }
}
