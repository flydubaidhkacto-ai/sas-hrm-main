<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        User::get()->each(function ($user) {
            // Historical data normalization must not trigger current device hooks.
            $user->updateQuietly(['join_date' => carbon($user->join_date)->format('Y-m-d')]);
        });
        DB::statement("ALTER TABLE users MODIFY COLUMN join_date DATE NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN join_date DATE NULL;");
    }
};
