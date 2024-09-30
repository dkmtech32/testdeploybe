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
        Schema::create('group_training_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('group_training_id');
            $table->bigInteger('user_id');
            $table->string('status_request');
            $table->string('acconpanion')->nullable();
            $table->string('note')->nullable();
            $table->string('attendance')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_training_users');
    }
};
