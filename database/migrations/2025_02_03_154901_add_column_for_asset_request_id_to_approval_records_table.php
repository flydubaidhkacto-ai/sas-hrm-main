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
        Schema::table('approval_records', function (Blueprint $table) {
            $table->foreignId('asset_request_id')->nullable()->constrained('asset_requests')->nullOnDelete();
            $table->foreignId('inventory_item_request_id')->nullable()->constrained('inventory_item_requests')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_records', function (Blueprint $table) {
            $table->dropForeign(['asset_request_id']);
            $table->dropColumn('asset_request_id');

            // Inventory request
            $table->dropForeign(['inventory_item_request_id']);
            $table->dropColumn('inventory_item_request_id');
        });
    }
};
