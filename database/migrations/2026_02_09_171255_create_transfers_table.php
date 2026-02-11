<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_id')->constrained('accommodations')->onDelete('cascade');
            $table->string('client_name');
            $table->string('client_phone');
            $table->string('client_email')->nullable();

            // Transfer details
            $table->enum('transfer_type', ['airport_hotel', 'hotel_airport', 'hotel_event', 'event_hotel', 'city_transfer']);
            $table->enum('trip_type', ['one_way', 'round_trip'])->default('one_way');
            $table->date('transfer_date'); // Pickup date
            $table->time('pickup_time');
            $table->string('pickup_location');
            $table->string('dropoff_location');

            // Flight details for airport transfers
            $table->string('flight_number')->nullable();
            $table->time('flight_time')->nullable(); // Arrival/Departure time of the flight

            // Vehicle & Passengers
            $table->enum('vehicle_type', ['sedan', 'van', 'minibus', 'bus']);
            $table->integer('passengers')->default(1);

            // Return details (if round trip)
            $table->date('return_date')->nullable();
            $table->time('return_time')->nullable();

            // Administration
            $table->decimal('price', 10, 2)->default(0);
            $table->string('eticket_path')->nullable();
            $table->enum('status', ['pending', 'paid', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['wallet', 'bank', 'both'])->nullable();
            $table->enum('beneficiary_type', ['organizer', 'client'])->default('client');

            // Users linkage
            $table->foreignId('organizer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Client user account if exists
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
