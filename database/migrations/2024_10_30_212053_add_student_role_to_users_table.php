<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // update previous users to employee role
        User::where('role', '!=', 'super_admin')
            ->update(['role' => 'employee']);
        Schema::table('users', function (Blueprint $table) {
            $table->string('class')->nullable();
            $table->time('class_start_time')->nullable();
            $table->time('class_end_time')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_relation')->nullable();
        });
        Schema::table('shifts', function (Blueprint $table) {
            $table->boolean('is_for_student')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('class');
            $table->dropColumn('class_start_time');
            $table->dropColumn('class_end_time');
            $table->dropColumn('guardian_name');
            $table->dropColumn('guardian_phone');
            $table->dropColumn('guardian_relation');
        });
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('is_for_student');
        });
    }
};
