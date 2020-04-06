<?php

namespace App\Observers;

use App\Models\Forum;
use App\Models\Obj;
use App\Models\Permission;
use Exception;
use Illuminate\Support\Str;

class ForumObserver
{
    /**
     * Handle the forum "created" event.
     *
     * @param Forum $forum
     * @return void
     */
    public function created(Forum $forum)
    {
        $perms = [
            [
                'title' => 'Kan ikke se',
                'description' => '',
            ],
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
                ->save((new Permission)->fill([
                    'level' => $j + 1,
                    'title' => $forum['title'] . ' - ' . $perms[$j]['title'],
                    'description' => $perms[$j]['description']
                ])
                );
        }
    }

    /**
     * Handle the forum "updated" event.
     *
     * @param Forum $forum
     * @return void
     */
    public function updated(Forum $forum)
    {
        $perms = (new Obj)
            ->find($forum['obj_id'])
            ->permissions;

        for ($j = 0; $j < count($perms); $j++) {
            $title = $perms[$j]['title'];
            $perms[$j]['title'] = Str::replaceFirst(
                    $title,
                    Str::beforeLast($title, ' - '),
                    $forum['title']
                ) . ' - ' . Str::afterLast($title, ' - ');

            $perms[$j]->save();
        }
    }

    /**
     * Handle the forum "deleted" event.
     *
     * @param Forum $forum
     * @return void
     * @throws Exception
     */
    public function deleted(Forum $forum)
    {
        (new Obj)
            ->find($forum['obj_id'])
            ->delete();

        return;
    }

    /**
     * Handle the forum "restored" event.
     *
     * @param Forum $forum
     * @return void
     */
    public function restored(Forum $forum)
    {
        //
    }

    /**
     * Handle the forum "force deleted" event.
     *
     * @param Forum $forum
     * @return void
     */
    public function forceDeleted(Forum $forum)
    {
        //
    }
}
