<?php

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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_paid')->default(1);
            $table->timestamps();
        });
        foreach(
            [
                'sick-leave' => [
                    'name' => 'Sick Leave',
                    'is_paid' => true,
                ], 
                'marriage-leave' => [
                    'name' => 'Marriage Leave',
                    'is_paid' => false,
                ],
                'vacation-leave' => [
                    'name' => 'Vacation Leave',
                    'is_paid' => true,
                ],
                'maternity-leave' => [
                    'name' => 'Maternity Leave',
                    'is_paid' => false,
                ],
                // Add more leave types as needed
                'personal-leave' => [
                    'name' => 'Personal Leave',
                    'is_paid' => false,
                ],
                'bereavement-leave' => [
                    'name' => 'Bereavement Leave',
                    'is_paid' => true,
                ],
                // ... add more leave types here
            ] as $type 
        )
        {
            \App\Models\LeaveType::create($type);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_types');
    }
};
