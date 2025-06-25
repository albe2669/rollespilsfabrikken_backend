<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePermPolicy
{
    use HandlesAuthorization;
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAnyCalendar(User $user)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAnyForum(User $user)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function add(User $user)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function addCalendar(User $user)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function addForum(User $user)
    {
        return $user->isSuperUser();
    }
}
