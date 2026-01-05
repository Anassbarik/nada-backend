<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Hotel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column exists
        if (!Schema::hasColumn('hotels', 'slug')) {
            Schema::table('hotels', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        // Generate slugs for existing hotels that don't have one
        Hotel::where(function($query) {
            $query->whereNull('slug')->orWhere('slug', '');
        })->chunk(100, function ($hotels) {
            foreach ($hotels as $hotel) {
                $baseSlug = Str::slug($hotel->name);
                $slug = $baseSlug;
                $count = 1;
                
                // Ensure uniqueness within the event
                while (Hotel::where('event_id', $hotel->event_id)
                    ->where('slug', $slug)
                    ->where('id', '!=', $hotel->id)
                    ->exists()) {
                    $slug = $baseSlug . '-' . $count;
                    $count++;
                }
                
                $hotel->slug = $slug;
                $hotel->saveQuietly();
            }
        });

        // Make it unique and non-nullable using raw SQL to handle existing constraint
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();
        
        // Drop unique index if it exists
        try {
            $connection->statement("ALTER TABLE `hotels` DROP INDEX `hotels_slug_unique`");
        } catch (\Exception $e) {
            // Index doesn't exist, that's fine
        }
        
        // Now modify the column to be unique and non-nullable
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
