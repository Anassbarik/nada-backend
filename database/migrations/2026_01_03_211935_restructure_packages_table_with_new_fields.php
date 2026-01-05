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
        // Check if table is called hotel_packages or packages
        $tableName = Schema::hasTable('hotel_packages') ? 'hotel_packages' : 'packages';
        
        // Delete all existing data since we're completely restructuring
        \DB::table($tableName)->truncate();
        
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            // Drop old columns if they exist
            $columnsToDrop = [];
            
            if (Schema::hasColumn($tableName, 'name')) {
                $columnsToDrop[] = 'name';
            }
            if (Schema::hasColumn($tableName, 'duration_days')) {
                $columnsToDrop[] = 'duration_days';
            }
            if (Schema::hasColumn($tableName, 'total_price')) {
                $columnsToDrop[] = 'total_price';
            }
            if (Schema::hasColumn($tableName, 'price')) {
                $columnsToDrop[] = 'price';
            }
            if (Schema::hasColumn($tableName, 'description')) {
                $columnsToDrop[] = 'description';
            }
            if (Schema::hasColumn($tableName, 'max_guests')) {
                $columnsToDrop[] = 'max_guests';
            }
            if (Schema::hasColumn($tableName, 'available_from')) {
                $columnsToDrop[] = 'available_from';
            }
            if (Schema::hasColumn($tableName, 'available_to')) {
                $columnsToDrop[] = 'available_to';
            }
            if (Schema::hasColumn($tableName, 'status')) {
                $columnsToDrop[] = 'status';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
        
        // Add new columns (now safe since table is empty)
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'nom_package')) {
                $table->string('nom_package')->after('hotel_id');
            }
            if (!Schema::hasColumn($tableName, 'type_chambre')) {
                $table->string('type_chambre')->after('nom_package');
            }
            if (!Schema::hasColumn($tableName, 'check_in')) {
                $table->date('check_in')->after('type_chambre');
            }
            if (!Schema::hasColumn($tableName, 'check_out')) {
                $table->date('check_out')->after('check_in');
            }
            if (!Schema::hasColumn($tableName, 'occupants')) {
                $table->integer('occupants')->default(1)->after('check_out');
            }
            if (!Schema::hasColumn($tableName, 'prix_ht')) {
                $table->decimal('prix_ht', 10, 2)->after('occupants');
            }
            if (!Schema::hasColumn($tableName, 'prix_ttc')) {
                $table->decimal('prix_ttc', 10, 2)->after('prix_ht');
            }
            if (!Schema::hasColumn($tableName, 'quantite_chambres')) {
                $table->integer('quantite_chambres')->default(1)->after('prix_ttc');
            }
            if (!Schema::hasColumn($tableName, 'chambres_restantes')) {
                $table->integer('chambres_restantes')->default(0)->after('quantite_chambres');
            }
            if (!Schema::hasColumn($tableName, 'disponibilite')) {
                $table->boolean('disponibilite')->default(true)->after('chambres_restantes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = Schema::hasTable('hotel_packages') ? 'hotel_packages' : 'packages';
        
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $table->dropColumn([
                'nom_package',
                'type_chambre',
                'check_in',
                'check_out',
                'occupants',
                'prix_ht',
                'prix_ttc',
                'quantite_chambres',
                'chambres_restantes',
                'disponibilite'
            ]);
        });
        
        Schema::table($tableName, function (Blueprint $table) {
            $table->string('name')->after('hotel_id');
            $table->integer('duration_days')->after('name');
            $table->decimal('total_price', 10, 2)->after('duration_days');
            $table->text('description')->nullable()->after('total_price');
            $table->integer('max_guests')->default(2)->after('description');
            $table->date('available_from')->after('max_guests');
            $table->date('available_to')->after('available_from');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('available_to');
        });
    }
};
