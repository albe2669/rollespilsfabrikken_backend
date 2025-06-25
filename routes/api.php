<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\PermissionController;
use App\Http\Controllers\Auth\PermissionRoleController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\Auth\SecurityQuestionController;
use App\Http\Controllers\Auth\UserAttributeController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\UserRoleController;
use App\Http\Controllers\Resources\CalendarController;
use App\Http\Controllers\Resources\CommentController;
use App\Http\Controllers\Resources\CommentFileController;
use App\Http\Controllers\Resources\EventController;
use App\Http\Controllers\Resources\ForumController;
use App\Http\Controllers\Resources\ForumPriorityController;
use App\Http\Controllers\Resources\PostAttributeController;
use App\Http\Controllers\Resources\PostController;
use App\Http\Controllers\Resources\PostFileController;
use App\Http\Controllers\Resources\ResourceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('signup', [AuthController::class, 'signup']);
    Route::get('activate/{token}', [AuthController::class, 'activate']);
    Route::post('/resend-email', [AuthController::class, 'resendEmail']);
    Route::get('/security-question', [SecurityQuestionController::class, 'show']);

    // Reset passwords
    Route::group([
        'prefix' => 'password',
    ], function () {
        Route::post('forgot', [PasswordResetController::class, 'create']);
        Route::get('find/{token}', [PasswordResetController::class, 'find']);
        Route::post('reset', [PasswordResetController::class, 'reset']);
    });

    Route::group([
        'middleware' => 'auth:sanctum',
    ], function () {

        Route::get('logout', [AuthController::class, 'logout']);

        // Get permissions related to object
        Route::get('/calendar/{calendar}/permission', [PermissionController::class, 'calendarIndex']);
        Route::get('/forum/{forum}/permission', [PermissionController::class, 'forumIndex']);

        // Add level permission from object to role
        Route::post('/calendar/{calendar}/level/{level}/role/{role}', [PermissionRoleController::class, 'calendarAdd']);
        Route::post('/forum/{forum}/level/{level}/role/{role}', [PermissionRoleController::class, 'forumAdd']);

        // Delete objects level permission from role
        Route::delete('/calendar/{calendar}/level/{level}/role/{role}', [PermissionRoleController::class, 'calendarDelete']);
        Route::delete('/forum/{forum}/level/{level}/role/{role}', [PermissionRoleController::class, 'forumDelete']);

        // Permissions
        Route::prefix('permission')->group(function () {
            Route::get('/', [PermissionController::class, 'index']);
            Route::get('/{permission}', [PermissionController::class, 'show']);
            Route::post('/{permission}/role/{role}', [PermissionRoleController::class, 'permissionAdd']);
            Route::delete('/{permission}/role/{role}', [PermissionRoleController::class, 'permissionDelete']);
        });

        // Add multiple permissions at the same time
        Route::post('/role/{role}/permissions', [PermissionRoleController::class, 'multiAdd']);
        Route::delete('/role/{role}/permissions', [PermissionRoleController::class, 'multiDelete']);

        // Create edit, delete roles
        Route::resource('role', RoleController::class);
        Route::prefix('role')->group(function () {// Get permissions from role in the context of an obj

            // Index permissions in roles
            Route::get('/{role}/forum/{forum}/permission', [PermissionRoleController::class, 'forumIndex']);
            Route::get('/{role}/calendar/{calendar}/permission', [PermissionRoleController::class, 'calendarIndex']);

            // Add permissions to roles in different ways.
            Route::post('/{role}/permission/{permission}', [PermissionRoleController::class, 'roleAdd']);
            Route::delete('/{role}/permission/{permission}', [PermissionRoleController::class, 'roleDelete']);
        });

        Route::prefix('user')->group(function () {
            // Assign, index and delete roles from user
            Route::get('/{user}/role', [UserRoleController::class, 'index']);
            Route::post('/{user}/role/{role}', [UserRoleController::class, 'add']);
            Route::delete('/{user}/role/{role}', [UserRoleController::class, 'delete']);
        });
    });
});

Route::group([
    'middleware' => 'auth:sanctum',
    'prefix' => 'user',
], function () {
    Route::get('/', [UserController::class, 'user']);

    // Index users
    Route::get('/index', [UserController::class, 'index']);

    // Update own username
    Route::patch('/username', [UserController::class, 'updateUsername']);

    // Permanently delete user
    Route::delete('/', [UserController::class, 'destroySelf']);
    Route::delete('/{user}', [UserController::class, 'destroy']);

    // Restrict access to forum without deleting user
    Route::post('/{user}/ban', [UserAttributeController::class, 'ban']);

    // Op to superuser
    Route::post('/{user}/op', [UserAttributeController::class, 'op']);

    // Reset user
    Route::post('/{user}/reset', [UserController::class, 'reset']);
    Route::delete('/{user}/clear', [UserController::class, 'clear']);

    // Avatar update
    Route::post('avatar', [UserController::class, 'avatar']);

    // Tokens
    Route::get('/token', [UserController::class, 'indexTokens']);
    Route::delete('/token/{token}/revoke', [UserController::class, 'revokeToken']);
});

Route::apiResource('securityQuestion', \App\Http\Controllers\Auth\SecurityQuestionController::class)->except([
    'show',
])->middleware('auth:sanctum');

Route::group([
    'middleware' => 'auth:sanctum',
], function () {
    // Register resources
    Route::apiResource('forum', ForumController::class);
    Route::apiResource('forum.post', PostController::class);
    Route::apiResource('forum.post.comment', CommentController::class);
    Route::apiResource('calendar', CalendarController::class);
    Route::apiResource('calendar.event', EventController::class);
    Route::apiResource('resource', ResourceController::class);
    Route::get('/events', [EventController::class, 'all']);

    // Pin posts
    Route::post('/forum/{forum}/post/{post}/pin', [PostAttributeController::class, 'pin']);

    // Lock posts
    Route::post('/forum/{forum}/post/{post}/lock', [PostAttributeController::class, 'lock']);

    // Pin comments
    Route::post('/forum/{forum}/post/{post}/comment/{comment}/pin', [CommentController::class, 'pin']);

    // Post files
    Route::post('/forum/{forum}/post/{post}/file', [PostFileController::class, 'file']);
    Route::get('/forum/{forum}/post/{post}/file/{file}', [PostFileController::class, 'getFile']);

    // Comment files
    Route::post('/forum/{forum}/post/{post}/comment/{comment}/file', [CommentFileController::class, 'file']);
    Route::get('/forum/{forum}/post/{post}/comment/{comment}/file/{file}', [CommentFileController::class, 'getFile']);

    // Get newest posts
    Route::get('/post', [PostController::class, 'newest']);

    // Check for event availability regarding both rooms, vehicles and times.
    Route::post('/calendar/{calendar}/event/check', [EventController::class, 'check']);

    // Forum priorities
    Route::put('/forums/priorities', [ForumPriorityController::class, 'priorities']);
    Route::put('/forum/{forum}/priority', [ForumPriorityController::class, 'priority']);
});
