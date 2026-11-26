<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appraisal_criteria', function (Blueprint $t) {
            $t->id();
            $t->unsignedTinyInteger('code')->unique();
            $t->string('section')->default('one'); // one | three
            $t->string('title');
            $t->text('low_text')->nullable();
            $t->text('mid_text')->nullable();
            $t->text('high_text')->nullable();
            $t->timestamps();
        });

        Schema::create('appraisals', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('employee_id')->index();
            $t->unsignedBigInteger('supervisor_id')->nullable()->index();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->string('period');
            $t->string('status')->default('pending_supervisor');
            $t->decimal('total_percent', 5, 2)->nullable();
            $t->decimal('final_marks', 5, 2)->nullable();
            $t->text('employee_comment')->nullable();
            $t->timestamp('employee_signed_at')->nullable();
            $t->timestamps();
        });

        Schema::create('appraisal_ratings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('appraisal_id')->constrained('appraisals')->cascadeOnDelete();
            $t->foreignId('criterion_id')->constrained('appraisal_criteria')->cascadeOnDelete();
            $t->string('level')->nullable(); // low|mid|high
            $t->unsignedTinyInteger('marks')->nullable(); // 1-10
            $t->text('comment')->nullable();
            $t->timestamps();
        });

        Schema::create('appraisal_job_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('appraisal_id')->constrained('appraisals')->cascadeOnDelete();
            $t->string('description');
            $t->boolean('is_satisfactory')->nullable();
            $t->string('standard')->nullable(); // BS|MS|ES
            $t->timestamps();
        });

        Schema::create('appraisal_reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('appraisal_id')->constrained('appraisals')->cascadeOnDelete();
            $t->string('stage'); // supervisor|hod|hr|md
            $t->unsignedBigInteger('reviewer_id')->nullable();
            $t->decimal('marks', 5, 2);
            $t->string('result_band');
            $t->string('recommendation');
            $t->unsignedTinyInteger('increment_steps');
            $t->text('comment')->nullable();
            $t->timestamp('signed_at')->nullable();
            $t->timestamps();
            $t->unique(['appraisal_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisal_reviews');
        Schema::dropIfExists('appraisal_job_items');
        Schema::dropIfExists('appraisal_ratings');
        Schema::dropIfExists('appraisals');
        Schema::dropIfExists('appraisal_criteria');
    }
};
