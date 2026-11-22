<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendances') && !Schema::hasColumn('attendances', 'roster_shift_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->unsignedBigInteger('roster_shift_id')->nullable()->after('shift_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendances') && Schema::hasColumn('attendances', 'roster_shift_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('roster_shift_id');
            });
        }
    }
};
