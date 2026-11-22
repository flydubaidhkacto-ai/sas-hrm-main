<?php

use App\Models\LeaveApplication;
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
        LeaveApplication::query()
            ->where('published_for_view', false)
            ->chunk(100, function ($apps) {
                foreach($apps as $app) {
                    if($app->status == 'approved' || $app->status === 'rejected') {
                        $app->update([
                            'published_for_view' => true,
                        ]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
