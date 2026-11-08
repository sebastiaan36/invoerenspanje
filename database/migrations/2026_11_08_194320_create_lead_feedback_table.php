<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Feedback van verloren leads. Alleen-toevoegen: rijen worden nooit gewijzigd of verwijderd.
        // Naam, e-mail en kenteken worden vastgelegd, zodat de feedback leesbaar blijft
        // als de lead ooit verdwijnt (lead_id wordt dan null).
        Schema::create('lead_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('lead_name');
            $table->string('lead_email');
            $table->string('kenteken')->nullable();
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_feedback');
    }
};
