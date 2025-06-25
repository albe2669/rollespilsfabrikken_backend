<?php

namespace App\Jobs\Test;

use App\Models\Calendar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $title = 'Job test';

    public string $description = 'Job test body';

    /**
     * Create a new job instance.
     */
    public function __construct($title, $description)
    {
        Log::debug('Constructing');
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug('Handling');
        (new Calendar)->create([
            'title' => $this->title,
            'description' => $this->description,
            'obj_id' => 90000,
        ]);
    }
}
