<?php

use Database\Seeders\Production\ForumProductionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class CreateForumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forums', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->efficientUuid('uuid')->index();
            $table->unsignedBigInteger('obj_id')->index();
            $table->string('title');
            $table->string('colour');
            $table->text('description')->nullable(true);
            $table->timestamps();

            $table
                ->foreign('obj_id')
                ->references('id')
                ->on('objs')
                ->onDelete('cascade');
        });

        Artisan::call('db:seed', [
            '--class' => ForumProductionSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forums');
    }
}
