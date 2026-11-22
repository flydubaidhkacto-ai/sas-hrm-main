<?php

use App\Models\Payslip;
use App\Models\PayslipPayment;
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
        try{
            Schema::table('payslip_payments', function (Blueprint $table) {
                $table->date('payslip_date')->nullable();
            });
        }catch(\Throwable $th) {}
        PayslipPayment::whereNotNull('payslip_id')
            ->chunk(100, function ($payslipPayments) {
                foreach ($payslipPayments as $payslipPayment) {
                    $payslip = Payslip::find($payslipPayment->payslip_id);
                    $payslipPayment->payslip_date = $payslip->month_date;
                    $payslipPayment->save();
                }
            });
        Schema::table('payslip_payments', function (Blueprint $table) {
            $table->dropForeign(['payslip_id']);
            $table->dropColumn('payslip_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payslip_payments', function (Blueprint $table) {
            $table->foreignId('payslip_id');
        });
        Payslip::chunk(100, function($slips) {
            foreach($slips as $slip) {
                PayslipPayment::query()
                    ->where('user_id', $slip->user_id)
                    ->where('payslip_date', $slip->month_date)
                    ->update([
                        'payslip_id' => $slip->id
                    ]);
            }
        });
        Schema::table('payslip_payments', function (Blueprint $table) {
            $table->dropColumn('payslip_date');
        });
    }
};
