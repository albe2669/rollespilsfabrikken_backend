<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserRole;

class UserObserver
{
    /**
     * Handle the user "created" event.
     *
     * @return void
     */
    public function created(User $user)
    {
        //
    }

    /**
     * Handle the user "updated" event.
     *
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the user "deleted" event.
     *
     * @return void
     */
    public function deleted(User $user)
    {
        (new UserRole)
            ->where('user_id', '=', $user['id'])
            ->get()
            ->each(function (UserRole $userRole, $key) {
                $userRole->delete();
            });

        $user
            ->tokens()
            ->each(function ($item, $key) {
                $item->delete();
            });
    }

    /**
     * Handle the user "restored" event.
     *
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
