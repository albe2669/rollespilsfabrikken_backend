<?php

namespace Database\Seeders\Production;

use App\Models\Obj;
use App\Models\SecurityQuestion;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Faker\Generator;

class SecurityQuestionProductionSeeder extends Seeder
{
    private function create($question, $answer) {
        (new SecurityQuestion())
            ->fill([
                'question' => $question,
                'answer' => $answer,
            ])->save();
    }

    public function run()
    {
        fake() = Faker::create();

        self::create('På hvilken vej lå den ægte fabrik?',                  'Glentevej');
        self::create('Hvilket år bliver JRK stiftet?',                      '2005');
        self::create('Hvad hedder den kvindelige rollespilsgruppe oprettet af kvinder fra rollespilsfabrikken?', 'Piger i panzer');
        self::create('Hvad er Fiffis rigtige fornavn?',                     'Jesper');
        self::create('Hvad er efternavnet på fabrikkens politiske vampyr?', 'Berner');
    }
}
