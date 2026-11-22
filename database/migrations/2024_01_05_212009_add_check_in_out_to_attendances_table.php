<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `attendances` MODIFY `worked_from` TIMESTAMP NULL;");
        DB::statement("ALTER TABLE `attendances` MODIFY `worked_to` TIMESTAMP NULL;");
        Schema::table('attendances', function (Blueprint $table) {
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['check_in', 'check_out']);
        });
    }
};
