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
        Schema::create('device_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_id', 255)->index(); // Unique device identifier
            $table->string('device_name')->nullable(); // Human readable device name
            $table->string('device_type')->nullable(); // mobile, web, tablet, etc.
            $table->string('platform')->nullable(); // iOS, Android, Windows, etc.
            $table->string('browser')->nullable(); // Chrome, Safari, etc.
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('session_token', 64)->unique()->index(); // For web sessions
            $table->foreignId('personal_access_token_id')->nullable()->constrained('personal_access_tokens')->onDelete('cascade'); // For API sessions
            $table->timestamp('last_activity')->nullable();
            $table->boolean('is_current')->default(false); // Mark current active session
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'device_id']);
            $table->index(['user_id', 'is_current']);
            $table->index('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_sessions');
    }
};
