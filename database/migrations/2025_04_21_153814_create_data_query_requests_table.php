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
        Schema::create('data_query_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('users', 'id');
            $table->foreignId('requested_to')->constrained('users', 'id');
            $table->enum('status', ['pending', 'answered', 'cancelled'])->default('pending');
            $table->string('query_for_model');
            $table->foreignId('query_for_model_id')->nullable();

            $table->string('query');
            $table->string('query_description')->nullable();

            $table->string('query_response')->nullable();
            $table->string('query_response_description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_query_requests');
    }
};
