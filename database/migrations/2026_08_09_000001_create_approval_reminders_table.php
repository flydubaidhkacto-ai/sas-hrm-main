<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_reminders', function (Blueprint $table) {
            $table->id();

            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            $table->unsignedBigInteger('approver_id');

            $table->string('reminder_type')->default('24_hour');
            $table->timestamp('sent_at');

            $table->unique(['approvable_type', 'approvable_id', 'approver_id', 'reminder_type'], 'approval_reminders_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_reminders');
    }
};
