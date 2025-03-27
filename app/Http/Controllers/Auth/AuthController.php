<?php

namespace App\Http\Controllers\Auth;

// Helpers
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\SignupRequest;
use App\Http\Requests\API\Auth\ResendEmailRequest;
use App\Http\Resources\User\User as UserResource;
use App\Models\Role;
use App\Models\SecurityQuestion;
use App\Models\User;
use App\Models\UserRole;
use App\Notifications\API\Auth\ActivationEmail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravolt\Avatar\Avatar;


// Notifications

// Requests

// Models

// Packages


class AuthController extends Controller
{
    /**
     * Register user
     *
     * @param string username
     * @param string email
     * @param string password
     * @return string activation_token // TODO: REMOVE THIS
     */
    public function signup(SignupRequest $request) {
        $request['password'] = Hash::make($request['password']);
        $request['activation_token'] = Str::random(60);

        $user = (new User)->create([
            'username' => $request['username'],
            'email' => $request['email'],
            'password' => $request['password'],
            'activation_token' => $request['activation_token']
        ]);

        $avatar = (new Avatar(config('laravolt.avatar')))
            ->setTheme('forum')
            ->create($user->username)
            ->getImageObject()
            ->encode('png');

        Storage::disk('local')->put('public/avatars/' . $user->uuid . '/avatar.png', (string) $avatar);

        $user->notify(new ActivationEmail());

        return response()->json([
            'message' => 'Successfully created user',
            //'token' => $user->activation_token, // TODO: REMOVE BEFORE PRODUCTION
        ], 201);
    }

    /**
     * Activate account from email token
     *
     * @param string token
     * @return JsonResponse
     */
    public function activate($token) {
        $user = User::where('activation_token', $token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid activation token'
            ], 404);
        }

        $user->active = true;
        $user->activation_token = '';
        $user->email_verified_at = Carbon::now();
        $user->save();

        // Give the user the medlem role, this gives all new users access to the forum
        $role = (new Role)
            ->where('title', '=', 'Medlem')
            ->first();

        if ($user->roles()->get()->where('id', '=', $role['id'])->first() === null) {
            $userRole = (new UserRole);
            $userRole->role()->associate($role);
            $userRole->user()->associate($user);
            $userRole->save();
        }

        return response()->json([
            'message' => 'Activated',
            'user' => new UserResource($user)
        ]);
    }

    /**
     * Login user
     *
     * @param LoginRequest $request
     * @return string access_token
     * @return string token_type
     * @return string expires_at
     */

    public function login(LoginRequest $request) {
        $credentials = Arr::only($request->validated(), ['email', 'password']);

        $user = (new User)->where('email', '=', $credentials['email'])->firstOrFail();

        if (!$user['active']) {
            return response()->json([
                'message' => 'Kontoen er ikke aktiveret'
            ], 401);
        }

        if ($user['deleted_at'] !== null) {
            return response()->json([
                'message' => 'Kontoen er bannet eller slettet'
            ], 401);
        }

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = $request->user();

        if ($request->device_name) {
            $token = $user->createToken($request->device_name . ' - API token');
        } else {
            $token = $user->createToken('API token');
        }

        return response()->json([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer'
        ], 200);
    }

    /**
     * Log the user out
     *
     * @param Request $request
     * @return string message
     */
    public function logout(Request $request) {
        $request->user()->tokens()->orderBy('last_used_at', 'desc')->firstOrFail()->delete();

        return response()->json([
            'message' => 'Logged out!'
        ], 200);
    }

    public function resendEmail(ResendEmailRequest $request) {
        $email = $request->validated()['email'];

        $user = (new User)->where('email', '=', $email)->firstOrFail();
        $user['activation_token'] = Str::random(60);
        $user->save();

        $user->notify(new ActivationEmail($user));

        return response()->json([
            'message' => 'Successfully resend email',
        ], 201);
    }
}
