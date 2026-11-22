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
        Schema::table('inventory_item_requests', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['inventory_item_id']);

            // Then drop the column
            $table->dropColumn('inventory_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_item_requests', function (Blueprint $table) {
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
        });
    }
};
