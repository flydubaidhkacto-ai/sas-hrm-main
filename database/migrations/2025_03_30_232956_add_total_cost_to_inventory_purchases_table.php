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
            Schema::table('inventory_purchases', function (Blueprint $table) {
                $table->double('total_cost')->nullable();
            });
        } catch (\Exception $e) {
            // Log::error($e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_purchases', function (Blueprint $table) {
            // $table->dropColumn('total_cost');
        });
    }
};
