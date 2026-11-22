<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE attendance_requests MODIFY COLUMN punch_in DATETIME NULL;');
        DB::statement('ALTER TABLE attendance_requests MODIFY COLUMN punch_out DATETIME NULL;');
        Schema::table('attendance_requests', function (Blueprint $table) {
            $table->dropColumn(['approved_by_id', 'rejected_by_id']);
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->foreignId('processed_by_id')->nullable();
            $table->dateTime('final_approved_at')->nullable();
            $table->dateTime('final_rejected_at')->nullable();
            $table->foreignId('final_processed_by_id')->nullable();
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_requests', function (Blueprint $table) {
            $table->foreignId('approved_by_id')->nullable();
            $table->foreignId('rejected_by_id')->nullable();
            $table->dropColumn(['approved_at', 'rejected_at', 'processed_by_id', 'final_approved_at', 'final_rejected_at', 'final_processed_by_id']);
            $table->dropColumn('notes');
        });
    }
};
