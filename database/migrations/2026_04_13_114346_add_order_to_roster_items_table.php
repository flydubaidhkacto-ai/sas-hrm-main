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
        Schema::table('roster_items', function (Blueprint $table) {
            // ✅ NEW: row order for drag-drop
            $table->integer('order')->default(0)->after('group');
        });
    }

    public function down(): void
    {
        Schema::table('roster_items', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
