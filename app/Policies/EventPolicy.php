<?php

namespace App\Policies;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isSuperUser()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view newest posts.
     *
     * @return mixed
     */
    public function viewAll(User $user)
    {
        return auth()->check();
    }

    /**
     * Determine whether the user can view any events.
     *
     * @return mixed
     */
    public function viewAny(User $user, Calendar $calendar)
    {
        return (new PolicyHelper)->checkLevel($user, $calendar['obj_id'], 2);
    }

    /**
     * Determine whether the user can view the event.
     *
     * @return mixed
     */
    public function view(User $user, Event $event)
    {
        return (new PolicyHelper)->checkLevel($user, $event->calendar['obj_id'], 2);
    }

    /**
     * Determine whether the user can create events.
     *
     * @return mixed
     */
    public function create(User $user, Calendar $calendar)
    {
        return (new PolicyHelper)->checkLevel($user, $calendar['obj_id'], 4);
    }

    /**
     * Determine whether the user can update the event.
     *
     * @return mixed
     */
    public function update(User $user, Event $event)
    {
        if ($event['user_id'] == $user['id']) {
            return true;
        }

        return (new PolicyHelper)->checkLevel($user, $event->calendar['obj_id'], 5);
    }

    /**
     * Determine whether the user can delete the event.
     *
     * @return mixed
     */
    public function delete(User $user, Event $event)
    {
        if ($event['user_id'] == $user['id']) {
            return true;
        }

        return (new PolicyHelper)->checkLevel($user, $event->calendar['obj_id'], 5);
    }

    /**
     * Determine whether the user can restore the event.
     *
     * @return mixed
     */
    public function restore(User $user, Event $event)
    {
        return (new PolicyHelper)->checkLevel($user, $event->calendar['obj_id'], 5);
    }

    /**
     * Determine whether the user can permanently delete the event.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Event $event)
    {
        return (new PolicyHelper)->checkLevel($user, $event->calendar['obj_id'], 5);
    }
}
