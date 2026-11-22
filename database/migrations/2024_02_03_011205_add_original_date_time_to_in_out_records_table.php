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
        Schema::table('in_out_records', function (Blueprint $table) {
            $table->timestamp('original_date_time')->nullable()
                ->comment("The other date_time column might get edited by user. but this will be the raw data and can be used to make a punch unique.");
        });
        DB::statement("UPDATE in_out_records SET original_date_time = date_time;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('in_out_records', function (Blueprint $table) {
            $table->dropColumn('original_date_time');
        });
    }
};
