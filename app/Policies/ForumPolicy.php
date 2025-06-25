<?php

namespace App\Policies;

use App\Models\Forum;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForumPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isSuperUser()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any forums.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the forum.
     *
     * @return mixed
     */
    public function view(User $user, Forum $forum)
    {
        return (new PolicyHelper)->checkLevel($user, $forum['obj_id'], 2);
    }

    /**
     * Determine whether the user can create forums.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can update the forum.
     *
     * @return mixed
     */
    public function update(User $user, Forum $forum)
    {
        if ($user->isSuperUser()) {
            return $user->isSuperUser();
        } else {
            return (new PolicyHelper)->checkLevel($user, $forum['obj_id'], 6);
        }

    }

    /**
     * Determine whether the user can update the forums priority.
     *
     * @return mixed
     */
    public function updatePriorities(User $user)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can delete the forum.
     *
     * @return mixed
     */
    public function delete(User $user, Forum $forum)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can restore the forum.
     *
     * @return mixed
     */
    public function restore(User $user, Forum $forum)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can permanently delete the forum.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Forum $forum)
    {
        return $user->isSuperUser();
    }
}
