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
        Schema::create('provident_fund_payment_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('paid_amount', 10, 2)->comment('Amount paid to employee');
            $table->decimal('total_balance', 10, 2)->comment('Total provident fund balance at time of payment');
            $table->decimal('remaining_balance', 10, 2)->comment('Remaining balance after payment');
            $table->text('note')->nullable()->comment('Payment note or reason');
            $table->foreignId('paid_by')->constrained('users')->comment('Admin who processed the payment');
            $table->timestamp('paid_at')->comment('When the payment was made');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provident_fund_payment_records');
    }
};
