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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->constrained('student_classes')->onDelete('set null');
            $table->string('roll')->nullable();
            $table->string('registration')->nullable();
        });

        User::students()
            ->whereNotNull('class')
            ->distinct()
            ->pluck('class')
            ->each(function ($className) {
                $class = \App\Models\StudentClass::firstOrCreate(['name' => $className]);
                User::students()
                    ->where('class', $className)
                    ->update(['class_id' => $class->id]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropColumn(['class_id', 'roll', 'registration']);
        });
    }
};
