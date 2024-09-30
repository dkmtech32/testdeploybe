<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('group_trainings', function (Blueprint $table) {
            $table->integer('number_of_courts')->nullable();
            $table->string('payment')->nullable();
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_trainings', function (Blueprint $table) {
            $table->dropColumn('number_of_courts');
            $table->dropColumn('payment');
        });
    }
};
