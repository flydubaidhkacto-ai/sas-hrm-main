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
        $superDepartments = \App\Models\SuperDepartment::all();
        $nameToId = [];
        foreach ($superDepartments as $sd) {
            $nameToId[trim($sd->name)] = $sd->id;
        }

        $assets = \App\Models\Asset::whereNotNull('location')->where('location', '!=', '')->whereNull('location_id')->get();
        foreach ($assets as $asset) {
            $locationText = trim($asset->location);
            if (isset($nameToId[$locationText])) {
                $asset->location_id = $nameToId[$locationText];
                $asset->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Asset::query()->update(['location_id' => null]);
    }
};
