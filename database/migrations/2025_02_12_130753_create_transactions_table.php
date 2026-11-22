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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->foreignId('to_account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('transaction_type', ['debit', 'credit']);
            $table->enum('transaction_category', ['sale', 'purchase', 'expense', 'revenue', 'other']);
            $table->enum('transaction_status', ['pending', 'approved', 'rejected']);
            $table->enum('transaction_method', ['cash', 'bank', 'credit_card', 'other']);
            $table->enum('cash_flow_category', ['operating', 'investing', 'financing'])->nullable();
            $table->string('transaction_number')->nullable();
            $table->dateTime('transaction_date');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
