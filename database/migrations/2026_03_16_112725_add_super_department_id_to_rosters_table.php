<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rosters', function (Blueprint $table) {

            $table->foreignId('super_department_id')
                ->nullable()
                ->after('name')
                ->constrained('super_departments')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('rosters', function (Blueprint $table) {

            $table->dropForeign(['super_department_id']);
            $table->dropColumn('super_department_id');

        });
    }
};
