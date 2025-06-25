<?php

namespace App\Observers;

use App\Models\Forum;
use App\Models\Obj;
use App\Models\Permission;
use Exception;

class ForumObserver
{
    /**
     * Handle the forum "created" event.
     *
     * @return void
     */
    public function created(Forum $forum)
    {
        $perms = [
            [
                'title' => 'Kan se',
                'description' => '',
            ],
            [
                'title' => 'Kan kommentere',
                'description' => '',
            ],
            [
                'title' => 'Kan oprette',
                'description' => '',
            ],
            [
                'title' => 'Kan moderere',
                'description' => '',
            ],
            [
                'title' => 'Kan administrere',
                'description' => '',
            ],
        ];

        for ($j = 0; $j < count($perms); $j++) {
            (new Obj)
                ->find($forum['obj_id'])
                ->permissions()
                ->save(
                    (new Permission)->fill([
                        'level' => $j + 2,
                        'title' => $perms[$j]['title'],
                        'description' => $perms[$j]['description'],
                    ]),
                );
        }
    }

    /**
     * Handle the forum "updated" event.
     *
     * @return void
     */
    public function updated(Forum $forum) {}

    /**
     * Handle the forum "deleted" event.
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleted(Forum $forum)
    {
        (new Obj)
            ->find($forum['obj_id'])
            ->delete();

    }

    /**
     * Handle the forum "restored" event.
     *
     * @return void
     */
    public function restored(Forum $forum)
    {
        //
    }

    /**
     * Handle the forum "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(Forum $forum)
    {
        //
    }
}
