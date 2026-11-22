<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('user_edits', function (Blueprint $table) {
            $table->bigInteger('primary_approved_by')->unsigned()->nullable()->after('approve');
            $table->foreign('primary_approved_by')->references('id')->on('users')->onDelete('set null');
            
            $table->bigInteger('final_approved_by')->unsigned()->nullable()->after('primary_approved_by');
            $table->foreign('final_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_edits', function (Blueprint $table) {
            $table->dropForeign(['primary_approved_by']);
            $table->dropColumn('primary_approved_by');
            
            $table->dropForeign(['final_approved_by']);
            $table->dropColumn('final_approved_by');
        });
    }
};
