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
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        });
        Schema::table('loans', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        });
        Schema::table('fines', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        });
        Schema::create('approval_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_id')->nullable();
            $table->foreignId('fine_id')->nullable();
            $table->foreignId('loan_id')->nullable();
            $table->foreignId('approval_step_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_records');
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('fines', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
