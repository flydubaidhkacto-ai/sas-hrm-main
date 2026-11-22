<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('station_leave_applications', function (Blueprint $table) {
            $table->id();

            $table->date('start_date');
            $table->date('end_date');

            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('leave_id')->nullable();

            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();

            $table->unsignedBigInteger('processed_by')->nullable();

            $table->boolean('send_employee_sms')->default(false);

            $table->dateTime('final_approved_at')->nullable();
            $table->dateTime('final_rejected_at')->nullable();
            $table->unsignedBigInteger('final_processed_by')->nullable();

            $table->dateTime('second_approved_at')->nullable();
            $table->dateTime('second_rejected_at')->nullable();
            $table->unsignedBigInteger('second_proccessed_by')->nullable();

            $table->text('second_approver_note')->nullable();
            $table->text('first_approver_note')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->string('attachment')->nullable();
            $table->string('attachment_name')->nullable();

            $table->double('duration_count')->nullable();
            $table->longText('applied_dates')->nullable();

            $table->boolean('published_for_view')->default(false);

            $table->unsignedBigInteger('current_approver_id')->nullable();

            $table->string('station');
            $table->string('present_address');
            $table->string('address_during_out_of_station');

            $table->string('contact_number');
            $table->string('alternative_number')->nullable();

            $table->text('reason');
            $table->text('remarks_by_employee')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->index('second_proccessed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('station_leave_applications');
    }
};
