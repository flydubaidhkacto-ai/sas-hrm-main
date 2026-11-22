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
        Schema::table('payslips', function (Blueprint $table) {
            $table->json('system_notes_json')->nullable();
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_in_roster')->default(false);
            $table->foreignId('roster_id')->nullable()->constrained('rosters');
        });
        Schema::create('attendance_roster_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
            $table->foreignId('roster_item_id')->constrained('roster_items')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('is_in_roster');
            $table->dropForeign(['roster_id']);
            $table->dropColumn('roster_id');
        });
        Schema::dropIfExists('attendance_roster_item');
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn('system_notes_json');
        });
    }
};
