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
        Schema::table('inventory_item_request_items', function (Blueprint $table) {
            $table->string('barcode')->nullable();
        });
        Schema::table('unique_barcodes', function (Blueprint $table) {
            $table->foreignId('inventory_item_request_item_id')->nullable();
            $table->foreignId('asset_request_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_item_request_items', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
        Schema::table('unique_barcodes', function (Blueprint $table) {
            $table->dropColumn('inventory_item_request_item_id');
            $table->dropColumn('asset_request_id');
        });
    }
};
