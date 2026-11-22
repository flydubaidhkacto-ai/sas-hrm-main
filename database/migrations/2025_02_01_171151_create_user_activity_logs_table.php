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
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('model_name');
            $table->tinyInteger('status')->default(0)->comment('visited -> 0, Create -> 1, Update -> 2, Delete -> 3');
            $table->string('visited_section_name')->nullable();
            $table->longText('operation_data')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete(); // Make user_id nullable
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
