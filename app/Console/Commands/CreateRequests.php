<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:requests {directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates requests for a model';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo 'Creating index; ';
        $this->call('make:request', [
            'name' => $this->argument('directory').'\Index',
        ]);

        echo 'Creating store; ';
        $this->call('make:request', [
            'name' => $this->argument('directory').'\Store',
        ]);

        echo 'Creating update; ';
        $this->call('make:request', [
            'name' => $this->argument('directory').'\Update',
        ]);

        echo 'Creating destroy; ';
        $this->call('make:request', [
            'name' => $this->argument('directory').'\Destroy',
        ]);

        echo 'Creating show; ';
        $this->call('make:request', [
            'name' => $this->argument('directory').'\Show',
        ]);

        echo 'use App\\Http\\Requests\\'.$this->argument('directory').'\Index;'."\n";
        echo 'use App\\Http\\Requests\\'.$this->argument('directory').'\Store;'."\n";
        echo 'use App\\Http\\Requests\\'.$this->argument('directory').'\Update;'."\n";
        echo 'use App\\Http\\Requests\\'.$this->argument('directory').'\Destroy;'."\n";
        echo 'use App\\Http\\Requests\\'.$this->argument('directory').'\Show;'."\n";
    }
}
