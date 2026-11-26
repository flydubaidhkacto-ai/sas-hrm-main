<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // NOT users.role (that column already drives SAS HRM permissions).
            if (!Schema::hasColumn('users', 'appraisal_role')) {
                $table->string('appraisal_role')->nullable(); // supervisor | hod | hr | md
            }
            if (!Schema::hasColumn('users', 'appraisal_supervisor_id')) {
                $table->unsignedBigInteger('appraisal_supervisor_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['appraisal_role', 'appraisal_supervisor_id']);
        });
    }
};
