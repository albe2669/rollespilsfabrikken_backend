<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Post\Lock;
use App\Http\Requests\API\Post\Pin;
use App\Http\Resources\Post\Post as PostResource;
use App\Models\Forum;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PostAttributeController extends Controller
{
    /**
     * Pin the post
     * Url : /api/forum/{forum}/post/{post}/pin
     *
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
            'post' => new PostResource($post),
        ], 200);
    }

    /**
     * Lock the post
     * Url : /api/forum/{forum}/post/{post}/lock
     *
     * @return JsonResponse
     */
    public function lock(Lock $request, Forum $forum, Post $post)
    {
        if ($post->locked) {
            $post->locked = false;
        } else {
            $post->locked = true;
        }

        $post->save();

        return response()->json([
            'message' => 'success',
            'post' => new PostResource($post),
        ], 200);
    }
}
