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
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();

            // Email basic info
            $table->string('from_email');
            $table->json('to_email');
            $table->json('cc_email')->nullable();
            $table->json('bcc_email')->nullable();
            $table->string('subject');
            $table->string('module');
            // Mail body / template info
            $table->longText('body')->nullable();
            $table->json('attachments');

            // Status tracking
            $table->enum('status', ['pending', 'sent', 'failed'])
                ->default('pending');

            // Error handling
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('attempt')->default(0);

            // Extra meta (queue, user, model reference etc.)
            $table->json('meta')->nullable();

            // When mail was actually sent
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index(['module']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_logs');
    }
};
