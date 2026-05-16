<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // Klantgegevens uit het formulier
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('woonplaats_spanje');
            $table->string('expected_move_date')->nullable();
            $table->text('comment')->nullable();

            // Voertuig + offerte-keuze
            $table->string('kenteken', 12);
            $table->string('package_slug');
            $table->boolean('residency_change')->default(false);
            $table->string('autonomia')->default('default');

            // Snapshot van de calculator op moment van aanvraag
            $table->unsignedInteger('bpm_teruggave_indicatie_eur')->nullable();
            $table->unsignedInteger('import_kosten_indicatie_eur')->nullable();
            $table->unsignedInteger('totaalprijs_indicatie_eur')->nullable();

            // Tracking
            $table->string('source')->default('organic');
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();

            // Status zoals plan.md §171: nieuw|gecontacteerd|offerte|gewonnen|verloren
            $table->string('status')->default('nieuw');

            $table->timestamps();

            $table->index('status');
            $table->index('email');
            $table->index('kenteken');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
