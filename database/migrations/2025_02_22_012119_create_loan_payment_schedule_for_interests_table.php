<?php

use App\Models\Loan;
use App\Models\Setting;
use App\Services\LoanService;
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
        Schema::dropIfExists('loan_payment_schedule_for_interests');
        Schema::create('loan_payment_schedule_for_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->foreignId('loan_payment_id')->nullable();
            $table->integer('month')->comment('Month number/index of the loan');
            $table->decimal('amount', 10, 2);
            $table->decimal('principal', 10, 2);
            $table->decimal('interest', 10, 2);
            $table->decimal('remaining', 10, 2);
            $table->timestamps();
        });
        // Import the existing data from loan_payments table
        Loan::query()
            ->where('cut_off_as', 'monthly')
            ->where('cut_off_amount', '>', 0)
            ->chunk(50, function ($loans) {
                foreach ($loans as $loan) {
                    $schedule = LoanService::generateFixedRateSchedule(
                        $loan->amount,
                        Setting::loan_interest_rate(),
                        $loan->cut_off_amount,
                        $loan->tenure_in_months
                    );
                    $payments = $loan->paid_amount_till_now->toArray();
                    foreach ($schedule as $item) {
                        $loan->paymentSchedules()->create([
                            'amount' => $item['emi'],
                            'principal' => $item['principal_paid'],
                            'interest' => $item['interest_paid'],
                            'remaining' => $item['remaining_balance'],
                            'loan_payment_id' => $payments[$item['month'] - 1]['id'] ?? null,
                            'month' => $item['month'],
                        ]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_payment_schedule_for_interests');
    }
};
