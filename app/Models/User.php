<?php

namespace App\Models;

use Dyrynda\Database\Support\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 *
 * @property int $id
 * @property string $uuid
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $avatar
 * @property string $activation_token
 * @property string $remember_token
 * @property bool $active
 * @property bool $super_user
 * @property Carbon $email_verified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @mixin Builder
 */
class User extends Authenticatable
{
    use GeneratesUuid;
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'active',
        'activation_token',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'activation_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'uuid' => EfficientUuid::class,
        'active' => 'boolean',
        'super_user' => 'boolean',
    ];

    protected $appends = [
        'avatar_url',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function getAvatarUrlAttribute()
    {
        return asset('storage/avatars/'.$this->uuid.'/'.$this->avatar);
    }

    public function roles()
    {
        return $this->hasManyThrough(Role::class, UserRole::class, 'user_id', 'id', 'id', 'role_id');
    }

    public function permissions()
    {
        $roles = $this->roles()->get();

        $perms = collect();
        foreach ($roles as $role) {
            $perm = $role->permissions()->get();
            $perms = $perms->merge($perm);
            $perms = $perms->unique('id');
        }
        $perms = collect($perms);

        return $perms->unique()->values()->all();
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function isSuperUser()
    {
        return $this->super_user;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
