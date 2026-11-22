<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->timestamp('issue_date')->nullable()->after('created_at');
        });

        // Copy existing created_at values to issue_date
        DB::table('loans')->update(['issue_date' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Restore created_at values from issue_date before dropping the column
        DB::table('loans')->update(['created_at' => DB::raw('issue_date')]);

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('issue_date');
        });
    }
};
