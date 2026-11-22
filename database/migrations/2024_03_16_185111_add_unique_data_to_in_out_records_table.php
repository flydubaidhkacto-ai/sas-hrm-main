<?php

use App\Models\InOutRecord;
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
        Schema::table('in_out_records', function (Blueprint $table) {
            $table->string('unique_data')->unique()->nullable();
        });
        try {
            InOutRecord::latest()
                ->chunk(1000, function ($inOutRecords) {
                    foreach ($inOutRecords as $inOutRecord) {
                        $inOutRecord->unique_data = $inOutRecord->original_date_time . '-' . $inOutRecord->user_id;
                        $inOutRecord->save();
                    }
                });
        } catch (\Throwable $th) {
            // $this->down();
            // throw $th;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('in_out_records', function (Blueprint $table) {
            $table->dropColumn('unique_data');
        });
    }
};
