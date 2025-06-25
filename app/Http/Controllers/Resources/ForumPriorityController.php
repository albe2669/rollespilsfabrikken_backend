<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Forum\Priorities;
use App\Http\Requests\API\Forum\Priority;
use App\Http\Resources\Forum\Forum as ForumResource;
use App\Models\Forum;

class ForumPriorityController extends Controller
{
    private function setPriority(Forum $forum, $priority): Forum
    {
        $forum->priority = $priority;
        $forum->save();

        return $forum->refresh();
    }

    public function priority(Priority $request, Forum $forum)
    {
        self::setPriority($forum, $request->validated()['priority']);

        return response()->json([
            'message' => 'success',
            'forum' => new ForumResource(self::setPriority($forum, $request->validated()['priority'])),
        ], 200);
    }

    public function priorities(Priorities $request)
    {
        $data = $request->validated();
        $forums = [];

        foreach ($data as $element) {
            $forum = Forum::whereUuid($element['id'])->first();

            if (! $forum) {
                return response()->json([
                    'message' => 'Could not find forum with id: '.$element['id'],
                    'input' => $element,
                ], 404);
            }

            $forums[] = $forum;
        }

        for ($i = 0; $i < count($forums); $i++) {
            $forums[$i] = self::setPriority($forums[$i], $data[$i]['priority']);
        }

        return response()->json([
            'message' => 'success',
            'forums' => ForumResource::collection($forums),
        ], 200);
    }
}
