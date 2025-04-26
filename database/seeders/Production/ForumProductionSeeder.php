<?php

namespace Database\Seeders\Production;

use App\Models\Forum;
use App\Models\Obj;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Faker\Generator;

class ForumProductionSeeder extends Seeder
{
    private Generator $faker;

    private function create($title, $description) {
        (new Forum())
            ->fill([
                'title' => $title,
                'description' => $description,
                'colour' => fake()->hexColor
            ])
            ->obj()
            ->associate((new Obj)->create([
                'type' => 'forum'
            ]))->save();
    }

    public function run()
    {
        fake() = Faker::create();

        self::create('Rude Skov',           '');
        self::create('Amager Fælled',       '');
        self::create('Den Magiske Skole',   '');
        self::create('Nøglebærere',         '');
        self::create('Andet',               '');
    }
}
