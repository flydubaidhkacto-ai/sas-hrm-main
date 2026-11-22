<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add column if it doesn't already exist
        if (!Schema::hasColumn('loans', 'status')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('decided_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('loans', 'status')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
