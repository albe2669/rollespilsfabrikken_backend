<?php

namespace App\Observers;

use App\Models\Event;

class EventObserver
{
    /**
     * Handle the event "created" event.
     *
     * @return void
     */
    public function created(Event $event) {}

    /**
     * Handle the event "updated" event.
     *
     * @return void
     */
    public function updated(Event $event) {}

    /**
     * Handle the event "deleted" event.
     *
     * @return void
     */
    public function deleted(Event $event) {}

    /**
     * Handle the event "restored" event.
     *
     * @return void
     */
    public function restored(Event $event) {}

    /**
     * Handle the event "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(Event $event) {}
}
