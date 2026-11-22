<?php

use App\Models\PerformanceCategory;
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
        Schema::create('performance_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('from_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->double('max_value')->default(10);
            $table->timestamps();
        });
        foreach ([
            //['name' => 'Present', 'from_system' => true, 'max_value' => 1, 'is_active' => true],
            //['name' => 'Absent', 'from_system' => true, 'max_value' => 1, 'is_active' => true],
            //['name' => 'Late', 'from_system' => true, 'max_value' => 1, 'is_active' => true],
            //['name' => 'Leave', 'from_system' => true, 'max_value' => 1, 'is_active' => true],
            //['name' => 'Paid Leave', 'from_system' => true, 'max_value' => 1, 'is_active' => true],
            //['name' => 'Unpaid Leave', 'from_system' => true, 'max_value' => 1, 'is_active' => true],
            ['name' => 'Punctuality', 'from_system' => False, 'is_active' => true],
            ['name' => 'Dress', 'from_system' => False, 'is_active' => true],
            ['name' => 'Hygiene', 'from_system' => False, 'is_active' => true],
            ['name' => 'Social', 'from_system' => False, 'is_active' => true],
            ['name' => 'Work Condition', 'from_system' => False, 'is_active' => true],
            ['name' => 'Communication', 'from_system' => False, 'is_active' => true],
        ] as $data) PerformanceCategory::create($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('performance_categories');
    }
};
