
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roster_shifts', function (Blueprint $table) {
            $table->boolean('display_only')->default(false)->after('end');
        });
    }

    public function down(): void
    {
        Schema::table('roster_shifts', function (Blueprint $table) {
            $table->dropColumn('display_only');
        });
    }
};
