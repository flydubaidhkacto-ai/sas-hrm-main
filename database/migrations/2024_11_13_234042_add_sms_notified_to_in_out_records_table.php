<?php

use App\Models\Attendance;
use App\Models\InOutRecord;
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
        Schema::table('in_out_records', function (Blueprint $table) {
            $table->boolean('sms_notified')->default(false);
            $table->boolean('admin_sms_notified')->default(false);
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('summary_sms_notified')->default(false);
            $table->boolean('admin_summary_sms_notified')->default(false);
        });
        InOutRecord::query()->update([
            'sms_notified' => true,
            'admin_sms_notified' => true,
        ]);
        Attendance::query()->update([
            'summary_sms_notified' => true,
            'admin_summary_sms_notified' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('in_out_records', function (Blueprint $table) {
            $table->dropColumn('sms_notified');
            $table->dropColumn('admin_sms_notified');
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('summary_sms_notified');
            $table->dropColumn('admin_summary_sms_notified');
        });
    }
};
