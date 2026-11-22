<?php

use App\Models\ProvidentFundPayment;
use App\Models\Setting;
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
        User::employees()->chunk(100, function ($emps) {
            foreach($emps as $emp) {
                $firstFund = ProvidentFundPayment::where('user_id', $emp->id)
                    ->orderBy('month_date', 'asc')
                    ->first();
                if($firstFund) {
                    $baseAmount = $firstFund->balance;
                    $last_balance_with_interest = $firstFund->balance_with_interest;
                    $pf_amount = 0;
                    foreach($emp->payroll_card?->deductions ?? [] as $head) {
                        if($head->category == 'provident_fund') {
                            $pf_amount = $head->amount;
                        }
                    }
                    $all = ProvidentFundPayment::where('user_id', $emp->id)
                        ->orderBy('month_date', 'asc')
                        ->where('id', '!=', $firstFund->id)
                        ->get();
                    foreach($all as $pf) {
                        $newBalance = $pf_amount + $pf->balance;
                        $rate = Setting::provident_fund_interest_rate();
                        if ($rate == 0 || $rate < 0) $interest_amount = 0;
                        else {
                            $closing_fund_balance = $last_balance_with_interest;
                            $interest_amount = ($closing_fund_balance * $rate / 12 / 100);
                        }
                        $sumOfPreviousFundInterests = ProvidentFundPayment::query()
                            ->where('user_id', $emp->id)
                            ->where('month_date', '<', $pf->month_date)
                            ->sum('interest_amount');

                        return $pf->update([
                            'balance' => $newBalance,
                            'interest_amount' => $interest_amount,
                            'interest' => $rate,
                            'balance_with_interest' => round($interest_amount + $sumOfPreviousFundInterests + $newBalance, 2),
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
