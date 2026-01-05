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
        Schema::table('bookings', function (Blueprint $table) {
            // Flight fields
            if (!Schema::hasColumn('bookings', 'flight_number')) {
                $table->string('flight_number')->nullable()->after('package_id');
            }
            if (!Schema::hasColumn('bookings', 'flight_date')) {
                $table->date('flight_date')->nullable()->after('flight_number');
            }
            if (!Schema::hasColumn('bookings', 'flight_time')) {
                $table->time('flight_time')->nullable()->after('flight_date');
            }
            if (!Schema::hasColumn('bookings', 'airport')) {
                $table->string('airport')->nullable()->after('flight_time');
            }
            
            // Client fields
            if (!Schema::hasColumn('bookings', 'full_name')) {
                $table->string('full_name')->nullable()->after('airport');
            }
            if (!Schema::hasColumn('bookings', 'company')) {
                $table->string('company')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('bookings', 'phone')) {
                $table->string('phone')->nullable()->after('company');
            }
            if (!Schema::hasColumn('bookings', 'email')) {
                $table->string('email')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('bookings', 'special_instructions')) {
                $table->text('special_instructions')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $columnsToDrop = ['flight_number', 'flight_date', 'flight_time', 'airport', 
                            'full_name', 'company', 'phone', 'email', 'special_instructions'];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
