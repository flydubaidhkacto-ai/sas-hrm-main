<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->foreignId('first_approve_id')->after('second_status')->nullable()->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignId('second_approve_id')->after('first_approve_id')->nullable()->constrained('users', 'id')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropForeign(['first_approve_id']);
            $table->dropForeign(['second_approve_id']);

            $table->dropColumn('first_approve_id');
            $table->dropColumn('second_approve_id');
        });
    }

};
