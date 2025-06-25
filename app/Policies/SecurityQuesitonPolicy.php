<?php

namespace App\Policies;

use App\Models\SecurityQuestion;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SecurityQuesitonPolicy
{
    use HandlesAuthorization;
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isSuperUser()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any security questions.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can view the forum.
     *
     * @return mixed
     */
    public function view(User $user, SecurityQuestion $securityQuestion)
    {
        return true;
    }

    /**
     * Determine whether the user can create security questions.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can update the security questions.
     *
     * @return mixed
     */
    public function update(User $user, SecurityQuestion $securityQuestion)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can delete the security questions.
     *
     * @return mixed
     */
    public function delete(User $user, SecurityQuestion $securityQuestion)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can restore the security questions.
     *
     * @return mixed
     */
    public function restore(User $user, SecurityQuestion $securityQuestion)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can permanently delete the security questions.
     *
     * @return mixed
     */
    public function forceDelete(User $user, SecurityQuestion $securityQuestion)
    {
        return $user->isSuperUser();
    }
}
