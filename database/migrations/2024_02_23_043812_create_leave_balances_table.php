<?php

use App\Models\Setting;
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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('paid_amount');
            $table->integer('unpaid_amount');
            $table->integer('year');
            $table->timestamps();
        });
        $stt = Setting::where('key', 'leave.paid_limit')->first();
        if($stt)
        {
            $stt->value = 30;
            $stt->save();
        }
        else 
        {
            Setting::create([
                'key' => 'leave.paid_limit',
                'value' => 30
            ]);
        }

        $stt = Setting::where('key', 'leave.unpaid_limit')->first();
        if($stt)
        {
            $stt->value = 30;
            $stt->save();
        }
        else 
        {
            Setting::create([
                'key' => 'leave.unpaid_limit',
                'value' => 30
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_balances');
    }
};
