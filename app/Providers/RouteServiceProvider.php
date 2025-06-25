<?php

namespace App\Providers;

use App\Models\Calendar;
use App\Models\Comment;
use App\Models\Event;
use App\Models\File;
use App\Models\Forum;
use App\Models\Obj;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Resource;
use App\Models\Role;
use App\Models\SecurityQuestion;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        Route::pattern('level', '[0-6]+');

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        Route::bind('calendar', function ($model) {
            return (new Calendar)->whereUuid($model)->first();
        });

        Route::bind('comment', function ($model) {
            return (new Comment)->whereUuid($model)->first();
        });

        Route::bind('event', function ($model) {
            return (new Event)->whereUuid($model)->first();
        });

        Route::bind('forum', function ($model) {
            return (new Forum)->whereUuid($model)->first();
        });

        Route::bind('obj', function ($model) {
            return (new Obj)->whereUuid($model)->first();
        });

        Route::bind('permission', function ($model) {
            return (new Permission)->whereUuid($model)->first();
        });

        Route::bind('post', function ($model) {
            return (new Post)->whereUuid($model)->first();
        });

        Route::bind('role', function ($model) {
            return (new Role)->whereUuid($model)->first();
        });

        Route::bind('user', function ($model) {
            return (new User)->whereUuid($model)->first();
        });

        Route::bind('securityQuestion', function ($model) {
            return (new SecurityQuestion)->whereUuid($model)->first();
        });

        Route::bind('file', function ($model) {
            return (new File)->whereUuid($model)->first();
        });

        Route::bind('resource', function ($model) {
            return (new Resource)->whereUuid($model)->first();
        });
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }
}
