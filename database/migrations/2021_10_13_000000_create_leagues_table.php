<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaguesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leagues', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('images')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('end_date_register')->nullable();
            $table->string('format_of_league');
            $table->integer('number_of_athletes');
            $table->string('image_background')->nullable();
            $table->time('start_time')->nullable();
            $table->string('national')->nullable();
            $table->string('image_nation_flag')->nullable();
            $table->integer('status')->nullable();
            $table->bigInteger('owner_id')->nullable();
            $table->string('location')->nullable();
            $table->integer('money')->nullable();
            $table->string('slug')->nullable();
            $table->string('type_of_league')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leagues');
    }
}
