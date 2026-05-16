<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossier_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users');

            // Snapshot van auteur-rol voor visuele weergave (klant|admin).
            $table->string('author_role', 16);

            $table->text('body');
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['dossier_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_messages');
    }
};
