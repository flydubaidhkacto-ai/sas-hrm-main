<?php

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
        /**
            0. Is station leave?
            1.⁠ ⁠Station:
            2.⁠ ⁠⁠from date:
            3.⁠ ⁠⁠to date:
            4.⁠ ⁠Present address:
            5.⁠ ⁠Address during out of station:
            6.⁠ ⁠Contact number:
            7.⁠ ⁠⁠Alternative no:
            8.⁠ ⁠⁠ Reason for Station Leave:
            9.⁠ ⁠⁠Remarks by employee
         */
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->boolean('is_station_leave')->default(false);
            $table->string('station')->nullable();
            $table->date('station_from_date')->nullable();
            $table->date('station_to_date')->nullable();
            $table->string('station_present_address')->nullable();
            $table->string('address_during_out_of_station')->nullable();
            $table->string('station_contact_number')->nullable();
            $table->string('station_alternative_number')->nullable();
            $table->text('reason_for_station_leave')->nullable();
            $table->text('station_remarks_by_employee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn([
                'is_station_leave',
                'station',
                'station_from_date',
                'station_to_date',
                'station_present_address',
                'address_during_out_of_station',
                'station_contact_number',
                'station_alternative_number',
                'reason_for_station_leave',
                'station_remarks_by_employee'
            ]);
        });
    }
};
