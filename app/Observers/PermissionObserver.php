<?php

namespace App\Observers;

use App\Models\Permission;
use App\Models\RolePerm;

class PermissionObserver
{
    /**
     * Handle the permission "created" event.
     *
     * @return void
     */
    public function created(Permission $permission)
    {
        //
    }

    /**
     * Handle the permission "updated" event.
     *
     * @return void
     */
    public function updated(Permission $permission)
    {
        //
    }

    /**
     * Handle the permission "deleted" event.
     *
     * @return void
     */
    public function deleted(Permission $permission)
    {
        (new RolePerm)
            ->where('permission_id', '=', $permission['id'])
            ->get()
            ->each(function (RolePerm $rolePerm, $item) {
                $rolePerm->delete();
            });
    }

    /**
     * Handle the permission "restored" event.
     *
     * @return void
     */
    public function restored(Permission $permission)
    {
        //
    }

    /**
     * Handle the permission "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(Permission $permission)
    {
        //
    }
}
