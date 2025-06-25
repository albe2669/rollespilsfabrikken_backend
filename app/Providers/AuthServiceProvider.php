<?php

namespace App\Providers;

use App\Models\Calendar;
use App\Models\Comment;
use App\Models\Event;
use App\Models\Forum;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Resource;
use App\Models\Role;
use App\Models\RolePerm;
use App\Models\SecurityQuestion;
use App\Models\User;
use App\Policies\CalendarPolicy;
use App\Policies\CommentPolicy;
use App\Policies\EventPolicy;
use App\Policies\ForumPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\PostPolicy;
use App\Policies\ResourcePolicy;
use App\Policies\RolePermPolicy;
use App\Policies\RolePolicy;
use App\Policies\SecurityQuesitonPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

// Models

// Policies

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        Forum::class => ForumPolicy::class,
        Post::class => PostPolicy::class,
        Calendar::class => CalendarPolicy::class,
        Event::class => EventPolicy::class,
        Comment::class => CommentPolicy::class,
        Permission::class => PermissionPolicy::class,
        Role::class => RolePolicy::class,
        RolePerm::class => RolePermPolicy::class,
        User::class => UserPolicy::class,
        SecurityQuestion::class => SecurityQuesitonPolicy::class,
        Resource::class => ResourcePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
