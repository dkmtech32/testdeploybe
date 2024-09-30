<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->integer('league_id');
            $table->string('match');
            $table->string('time');
            $table->string('set_1_team_1')->nullable();
            $table->string('set_1_team_2')->nullable();
            $table->string('set_2_team_1')->nullable();
            $table->string('set_2_team_2')->nullable();
            $table->string('set_3_team_1')->nullable();
            $table->string('set_3_team_2')->nullable();
            $table->string('result_team_1')->nullable();
            $table->string('result_team_2')->nullable();
            $table->string('round')->nullable();
            $table->integer('player1_team_1')->nullable();
            $table->integer('player2_team_1')->nullable();
            $table->integer('player1_team_2')->nullable();
            $table->integer('player2_team_2')->nullable();
            $table->dateTime('date');
            $table->string('stadium')->nullable();
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
