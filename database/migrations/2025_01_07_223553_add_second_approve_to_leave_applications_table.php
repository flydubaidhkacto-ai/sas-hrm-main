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
            $table->dateTime('second_approved_at')->nullable();
            $table->dateTime('second_rejected_at')->nullable();
            $table->foreignId('second_proccessed_by')->nullable()->constrained('users');
            $table->text('second_approver_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropForeign(['second_proccessed_by']);
            $table->dropColumn('second_approved_at');
            $table->dropColumn('second_rejected_at');
            $table->dropColumn('second_proccessed_by');
            $table->dropColumn('second_approver_note');
        });
    }
};
