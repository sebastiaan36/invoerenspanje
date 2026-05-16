<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();

            // Plan.md §154 statussen
            $table->string('status')->default('concept');
            // concept | offerte | akkoord | in_uitvoering | afgerond | geannuleerd

            // Voertuiggegevens (snapshot — RDW data kan wijzigen)
            $table->string('kenteken', 12);
            $table->string('merk')->nullable();
            $table->string('model')->nullable();
            $table->date('datum_eerste_toelating')->nullable();
            $table->string('brandstof')->nullable();
            $table->unsignedSmallInteger('co2')->nullable();
            $table->json('rdw_data_json')->nullable();

            // Pakket + indicaties
            $table->string('pakket'); // basis | compleet | compleet_plus
            $table->unsignedInteger('bpm_indicatie_eur')->nullable();
            $table->unsignedInteger('service_fee_eur')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('kenteken');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossiers');
    }
};
