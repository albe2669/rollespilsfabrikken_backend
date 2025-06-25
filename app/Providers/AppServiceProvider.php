<?php

namespace App\Providers;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\Forum;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Observers\CalendarObserver;
use App\Observers\EventObserver;
use App\Observers\ForumObserver;
use App\Observers\PermissionObserver;
use App\Observers\RoleObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Event::observe(EventObserver::class);
        Forum::observe(ForumObserver::class);
        Calendar::observe(CalendarObserver::class);
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);
        User::observe(UserObserver::class);
    }
}
