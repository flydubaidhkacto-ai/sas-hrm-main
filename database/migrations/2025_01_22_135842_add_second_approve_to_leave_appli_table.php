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
        try {
            Schema::table('leave_applications', function (Blueprint $table) {
                $table->dateTime('second_approved_at')->nullable();
                $table->dateTime('second_rejected_at')->nullable();
                $table->foreignId('second_processed_by')->nullable()->constrained('users');
                $table->text('second_approver_note')->nullable();
            });
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
