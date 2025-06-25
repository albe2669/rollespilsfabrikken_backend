<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\User\AvatarUpload;
use App\Http\Requests\API\Auth\User\Clear;
use App\Http\Requests\API\Auth\User\Destroy;
use App\Http\Requests\API\Auth\User\DestroySelf;
use App\Http\Requests\API\Auth\User\Index;
use App\Http\Requests\API\Auth\User\IndexTokens;
use App\Http\Requests\API\Auth\User\Reset;
use App\Http\Requests\API\Auth\User\RevokeToken;
use App\Http\Requests\API\Auth\User\UpdateUsername;
use App\Http\Resources\Token\Token;
use App\Http\Resources\User\LoggedInUser;
use App\Http\Resources\User\User as UserResource;
use App\Models\Comment;
use App\Models\Event;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    /**
     * Fetch the authed user
     *
     * @return JsonResponse
     */
    public function index(Index $request)
    {
        return response()->json([
            'message' => 'success',
            'users' => UserResource::collection(User::all()),
        ]);
    }

    /**
     * Fetch the authed user
     *
     * @return JsonResponse
     */
    public function user()
    {
        return response()->json([
            'message' => 'success',
            'user' => new LoggedInUser(auth()->user()),
        ]);
    }

    /**
     * Update the authed users username
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateUsername(UpdateUsername $request)
    {
        $user = auth()->user();

        $user->username = $request->validated()['username'];
        $user->save();

        return response()->json([
            'message' => 'success',
            'user' => new LoggedInUser(auth()->user()),
        ]);
    }

    /**
     * Delete the authed user
     *
     * @return JsonResponse
     */
    public function destroySelf(DestroySelf $request)
    {
        auth()->user()->delete();

        return response()->json([
            'message' => 'success',
        ]);
    }

    /**
     * Delete user
     *
     * @return JsonResponse
     */
    public function destroy(Destroy $request, User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'success',
        ]);
    }

    /**
     * Reset users credentials
     *
     * @return JsonResponse
     */
    public function reset(Reset $request, User $user)
    {
        $existingUser = User::where('email', '=', $request->validated()['email'])->first();

        if ($existingUser && $existingUser['id'] !== $user['id']) {
            return response()->json([
                'message' => 'Den email eksisterer allerede i systemet og hører ikke til den bruger der nulstilles',
            ], 400);
        }
        $user->email = $request->validated()['email'];
        $user->password = Hash::make(Str::random(40));

        $user->save();

        return response()->json([
            'message' => 'success',
            'user' => new UserResource($user->refresh()),
        ]);
    }

    /**
     * Clear all data created by the user
     *
     * @return JsonResponse
     */
    public function clear(Clear $request, User $user)
    {
        $user
            ->posts()
            ->each(function (\App\Models\Post $post, $key) {
                $post->delete();
            });

        $user
            ->comments()
            ->each(function (Comment $comment, $key) {
                $comment->delete();
            });

        $user
            ->events()
            ->each(function (Event $event, $key) {
                $event->delete();
            });

        return response()->json([
            'message' => 'success',
            'user' => new UserResource($user->refresh()),
        ]);
    }

    /**
     * Update avatar
     *
     * @return JsonResponse
     */
    public function avatar(AvatarUpload $request)
    {
        $user = auth()->user();

        $file = $request
            ->file('avatar');

        if ($file->getSize() > 256000000) {
            return response()->json([
                'message' => 'Fil må være max 256mb',
            ], 400);
        }

        $path = $file
            ->storeAs('public/avatars/'.$user->uuid, 'avatar.'.$file->extension());
        $user->avatar = 'avatar.'.$file->extension();
        $user->save();

        return response()->json([
            'message' => 'success',
            'user' => new UserResource($user->refresh()),
        ]);
    }

    /**
     * Update avatar
     *
     * @return JsonResponse
     */
    public function indexTokens(IndexTokens $request)
    {
        $user = auth()->user();

        return response()->json([
            'message' => 'success',
            'tokens' => Token::collection($user->tokens),
        ]);
    }

    /**
     * Update avatar
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function revokeToken(RevokeToken $request, PersonalAccessToken $token)
    {
        $user = auth()->user();

        $token->delete();

        return response()->json([
            'message' => 'success',
            'tokens' => Token::collection($user->tokens),
        ]);
    }
}
