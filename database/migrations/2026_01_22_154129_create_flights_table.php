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
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_id')->constrained('accommodations')->onDelete('cascade');
            $table->string('full_name'); // Nom complet de client
            $table->enum('flight_class', ['economy', 'business', 'first'])->default('economy'); // Classe
            $table->date('departure_date'); // Date départ
            $table->time('departure_time'); // Heure départ
            $table->date('arrival_date'); // Date arrivée
            $table->time('arrival_time'); // Heure arrivée
            $table->string('departure_flight_number'); // Vol Départ (e.g., "AT2222")
            $table->date('return_date')->nullable(); // Date retour
            $table->time('return_departure_time')->nullable(); // Heure départ retour
            $table->date('return_arrival_date')->nullable(); // Date arrivée retour
            $table->time('return_arrival_time')->nullable(); // Heure arrivée retour
            $table->string('return_flight_number')->nullable(); // Vol Retour (e.g., "AT1111")
            $table->string('reference')->unique(); // Flight Ref
            $table->string('eticket_path')->nullable(); // eTicket file path
            $table->enum('beneficiary_type', ['organizer', 'client'])->default('client'); // Beneficier type
            $table->foreignId('organizer_id')->nullable()->constrained('users')->onDelete('set null'); // If organizer
            $table->string('client_email')->nullable(); // If client
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Created user if client
            $table->string('credentials_pdf_path')->nullable(); // Credentials PDF path
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
