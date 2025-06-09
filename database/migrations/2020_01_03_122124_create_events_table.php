<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->efficientUuid('uuid')->index();
            $table->unsignedBigInteger('calendar_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('series_id')->nullable();
            $table->string('title');
            $table->text('description');
            $table->time('start');
            $table->unsignedBigInteger('event_length');
            $table->timestamps();

            $table
                ->foreign('calendar_id')
                ->references('id')
                ->on('calendars')
                ->onDelete('cascade');

            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
