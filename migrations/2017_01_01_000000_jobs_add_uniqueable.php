<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class JobsAddUniqueable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function($table) {
            $table->text('unique_id')->nullable();
            $table->unique(['queue', 'unique_id'], 'jobs_queue_unique_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropUnique('jobs_queue_unique_id_unique');
            $table->dropColumn('unique_id');
        });
    }
}
