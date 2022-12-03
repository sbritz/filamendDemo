<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('post_tag', function (Blueprint $table) {
            $table
                ->foreign('tag_id')
                ->references('id')
                ->on('tags')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table
                ->foreign('post_id')
                ->references('id')
                ->on('posts')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post_tag', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
            $table->dropForeign(['post_id']);
        });
    }
};
