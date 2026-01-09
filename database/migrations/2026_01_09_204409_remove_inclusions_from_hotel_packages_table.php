<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Before removing, migrate any existing inclusions from packages to hotels
        // Get all unique hotel_id and inclusions combinations
        $packageInclusions = DB::table('hotel_packages')
            ->whereNotNull('inclusions')
            ->select('hotel_id', 'inclusions')
            ->distinct()
            ->get();

        foreach ($packageInclusions as $package) {
            $inclusions = json_decode($package->inclusions, true);
            if ($inclusions && is_array($inclusions)) {
                // Update hotel with inclusions (merge if hotel already has some)
                $hotel = DB::table('hotels')->where('id', $package->hotel_id)->first();
                if ($hotel) {
                    $existingInclusions = $hotel->inclusions ? json_decode($hotel->inclusions, true) : [];
                    $mergedInclusions = array_unique(array_merge($existingInclusions ?? [], $inclusions));
                    DB::table('hotels')
                        ->where('id', $package->hotel_id)
                        ->update(['inclusions' => json_encode(array_values($mergedInclusions))]);
                }
            }
        }

        // Now remove the column from hotel_packages
        Schema::table('hotel_packages', function (Blueprint $table) {
            $table->dropColumn('inclusions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_packages', function (Blueprint $table) {
            $table->json('inclusions')->nullable()->after('disponibilite');
        });
    }
};
