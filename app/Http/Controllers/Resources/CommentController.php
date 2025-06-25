<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\FileHelpers;
use App\Http\Controllers\Helpers\Helpers;
use App\Http\Requests\API\Comment\Index;
use App\Http\Requests\API\Comment\Pin;
use App\Http\Requests\API\Comment\Show;
use App\Http\Requests\API\Comment\Store;
use App\Http\Requests\API\Comment\Update;
use App\Http\Resources\Comment\Comment as CommentResource;
use App\Http\Resources\Comment\CommentWithChildComments;
use App\Http\Resources\Comment\CommentWithChildCommentsCollection;
use App\Models\Comment;
use App\Models\Forum;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

// Models

// Requests

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Index $request, Forum $forum, Post $post)
    {
        $comments = $post
            ->comments()
            ->where('parent_id', '=', null)
            ->orderBy('pinned', 'desc')
            ->with('childComments')
            ->getQuery();

        $comments = (new Helpers)->filterItems($request, $comments);

        return response()->json([
            'message' => 'success',
            'data' => new CommentWithChildCommentsCollection($comments),
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show(Show $request, Forum $forum, Post $post, Comment $comment)
    {
        return response()->json([
            'message' => 'success',
            'comment' => new CommentWithChildComments($comment),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     */
    public function store(Store $request, Forum $forum, Post $post)
    {
        if ($post->locked) {
            return response()->json([
                'message' => 'Cannot create comments as the post is locked',
            ], 423);
        }

        $data = $request->validated();

        $comment = (new Comment)->fill($data);
        $comment->user()->associate(auth()->user());

        if (Arr::has($data, 'parent_id')) {
            $parentComment = Comment::whereUuid($data['parent_id'])->firstOrFail();

            if ($post['id'] != $parentComment['post_id']) {
                return response()->json([
                    'message' => 'The parent comment is not part of this post.',
                ], 400);
            } else {
                $comment->parent()->associate($parentComment);
            }
        }

        $post->comments()->save($comment);

        if ($request->hasFile(('files'))) {
            foreach ($request->file('files') as $file) {
                FileHelpers::saveCommentFile($file, $comment->refresh());
            }
        }

        return response()->json([
            'message' => 'success',
            'comment' => new CommentResource($comment->refresh()),
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return JsonResponse
     */
    public function update(Update $request, Forum $forum, Post $post, Comment $comment)
    {
        $comment->update($request->validated());

        return response()->json([
            'message' => 'success',
            'comment' => new CommentResource($comment),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse
     */
    public function destroy(Forum $forum, Post $post, Comment $comment)
    {
        $comment->delete();

        return response()->json([
            'message' => 'success',
        ], 200);
    }

    /**
     * Pin the specified comment.
     *
     * @return JsonResponse
     */
    public function pin(Pin $request, Forum $forum, Post $post, Comment $comment)
    {
        if ($comment->parent_id !== null) {
            return response()->json([
                'message' => 'You can only pin root comments',
            ], 400);
        }

        if ($comment->pinned) {
            $comment->pinned = false;
        } else {
            $comment->pinned = true;
        }

        $comment->save();

        return response()->json([
            'message' => 'success',
            'comment' => new CommentResource($comment),
        ], 200);
    }
}
