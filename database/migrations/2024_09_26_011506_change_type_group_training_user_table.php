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
        Schema::table('group_training_users', function (Blueprint $table) {
            $table->integer('acconpanion')->change();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_training_users', function (Blueprint $table) {
            $table->string('acconpanion')->change();
        });
    }
    
};
