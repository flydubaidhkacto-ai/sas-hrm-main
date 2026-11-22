<?php

use App\Models\LeaveType;
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
        Schema::table('leave_types', function (Blueprint $table) {
            $table->boolean('is_editable')->default(true);
        });
        LeaveType::create([
            'name' => 'Earn Leave',
            'description' => 'Earn Leave',
            'is_paid' => true,
            'is_for_all_user' => true,
            'bg_color' => '#bbcbff',
            'is_editable' => false,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('is_editable');
        });
        LeaveType::where('name', 'Earn Leave')
            ->where('is_for_all_user', true)
            ->where('is_editable', false)
            ->delete();
    }
};
