<?php

namespace App\Policies;

use App\Models\User;

class PolicyHelper
{
    public function getLevel(User $user, $objectId)
    {
        return collect($user->permissions())
            ->where('obj_id', '=', $objectId)
            ->pluck('level')
            ->max();
    }

    public function checkLevel(User $user, $objectId, $minimum)
    {
        $level = $this->getLevel($user, $objectId);

        if ($level >= $minimum) {
            return true;
        }

        return false;
    }
}
