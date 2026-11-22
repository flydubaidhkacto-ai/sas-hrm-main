<?php

use App\Models\Setting;
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
        Setting::query()
            ->chunk(100, function ($settings) {
                foreach ($settings as $setting) {
                    if ($setting->value == 0 && (
                        str_starts_with($setting->key, 'emp_leave_balance_') &&
                        !str_starts_with($setting->key, 'emp_leave_balance_0_')
                    )) {
                        dump($setting->key);
                        $setting->delete();
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
