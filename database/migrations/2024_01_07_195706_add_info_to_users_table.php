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
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->unique()->nullable()->index();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->text('address')->nullable();
            $table->string('religion')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('nid_or_passport')->nullable();
            $table->string('nid_or_passport_file')->nullable();
            $table->foreignId('super_department_id')->nullable();
            $table->string('employee_type')->nullable();
            $table->string('picture_file')->nullable();
            $table->string('signature_file')->nullable();
            $table->string('reference_name')->nullable();
            $table->string('reference_phone')->nullable();
            $table->string('emergency_name')->nullable();
            $table->string('emergency_relation')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('emergency_address')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id',
                'father_name',
                'mother_name',
                'address',
                'religion',
                'blood_group',
                'marital_status',
                'nationality',
                'nid_or_passport',
                'nid_or_passport_file',
                'super_department_id',
                'employee_type',
                'picture_file',
                'signature_file',
                'reference_name',
                'reference_phone',
                'emergency_name',
                'emergency_relation',
                'emergency_phone',
                'emergency_address',
                'is_active',
            ]);
        });
    }
};
