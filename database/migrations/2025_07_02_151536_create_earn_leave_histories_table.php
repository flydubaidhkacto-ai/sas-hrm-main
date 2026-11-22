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
        Schema::create('earn_leave_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->double('new_balance');
            $table->double('old_balance');
            $table->double('added');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earn_leave_histories');
    }
};
