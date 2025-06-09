<?php

namespace Database\Seeders\Production;

use App\Models\Forum;
use App\Models\Obj;
use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Database\Seeder;

class ForumProductionSeeder extends Seeder
{
    private Generator $faker;

    private function create($title, $description)
    {
        (new Forum)
            ->fill([
                'title' => $title,
                'description' => $description,
                'colour' => fake()->hexColor,
            ])
            ->obj()
            ->associate((new Obj)->create([
                'type' => 'forum',
            ]))->save();
    }

    public function run()
    {
        $this->create('Rude Skov', '');
        $this->create('Amager Fælled', '');
        $this->create('Den Magiske Skole', '');
        $this->create('Nøglebærere', '');
        $this->create('Andet', '');
    }
}
