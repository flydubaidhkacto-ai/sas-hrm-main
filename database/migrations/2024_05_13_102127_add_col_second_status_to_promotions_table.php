<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->string('second_status', 1)->nullable()->default(null)->after('status');
        });
    }


    public function down()
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('second_status');
        });
    }
};
