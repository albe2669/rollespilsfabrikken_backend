<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyPostFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('post_files', function (Blueprint $table) {
            $table->dropIndex('post_files_uuid_index');
            $table->dropColumn(['uuid', 'name', 'saved_name', 'file_size']);

            $table->unsignedBigInteger('file_id')->index();

            $table
                ->foreign('file_id')
                ->references('id')
                ->on('files')
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
        //
    }
}
