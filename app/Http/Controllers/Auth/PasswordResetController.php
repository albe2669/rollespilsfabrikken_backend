<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\Reset\RequestResetRequest;
use App\Http\Requests\API\Auth\Reset\ResetRequest;
use App\Http\Resources\User\User as UserResource;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\API\Auth\PasswordResetRequest;
use App\Notifications\API\Auth\PasswordResetSuccess;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Create token password reset
     *
     * @return JsonResponse [string] message
     */
    public function create(RequestResetRequest $request)
    {
        $user = (new User)->where('email', $request->validated()['email'])->firstOrFail();

        $passwordReset = (new PasswordReset)->updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Str::random(60),
            ],
        );
        if ($user && $passwordReset) {
            $user->notify(new PasswordResetRequest($passwordReset->token));
        }

        return response()->json([
            'message' => 'Email med link sendt!',
        ], 200);
    }

    /**
     * Find token password reset
     *
     * @param  [string] $token
     * @return JsonResponse [string] message
     *
     * @throws Exception
     */
    public function find($token)
    {
        $passwordReset = (new PasswordReset)->where('token', '=', $token)->firstOrFail();

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();

            return response()->json([
                'message' => 'Linket er udløbet',
            ], 498);
        }

        return response()->json($passwordReset);
    }

    /**
     * Reset password
     *
     * @return JsonResponse [string] message
     *
     * @throws Exception
     */
    public function reset(ResetRequest $request)
    {
        $data = $request->validated();
        $passwordReset = PasswordReset::where([
            ['token', $data['token']],
            ['email', $data['email']],
        ])->firstOrFail();

        $user = User::where('email', $passwordReset->email)->firstOrFail();

        $user->password = Hash::make($data['password']);
        $user->save();
        $user->notify(new PasswordResetSuccess);

        $passwordReset->delete();

        return response()->json([
            'message' => 'success',
            'user' => new UserResource($user->refresh()),
        ]);
    }
}
