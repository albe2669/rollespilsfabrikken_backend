<?php

use App\Models\Role;
use Database\Seeders\Production\RoleProductionSeeder;
use Faker\Factory as Faker;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class AddColorToRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('color');
        });

        $existing_roles = Role::where('color', '=', '')->get();
        $faker = Faker::create();

        foreach ($existing_roles as $role) {
            $role->color = $faker->hexColor;
            $role->save();
        }

        Artisan::call('db:seed', [
            '--class' => RoleProductionSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['color']);
        });
    }
}
