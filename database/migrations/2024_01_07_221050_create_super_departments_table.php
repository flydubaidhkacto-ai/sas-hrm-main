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
        Schema::create('super_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('super_department_id')->nullable()->constrained()->cascadeOnDelete();
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('added_manually_by')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('notes_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['super_department_id']);
            $table->dropColumn('super_department_id');
        });
        Schema::dropIfExists('super_departments');
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('added_manually_by');
            $table->dropColumn('notes');
            $table->dropColumn('notes_by');
        });
    }
};
