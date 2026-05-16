<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->cascadeOnDelete();

            $table->string('type'); // paspoort | nie | kentekenbewijs | coc | overig
            $table->string('filename');               // origineel
            $table->string('path');                   // op `local` disk (private)
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');

            // Plan.md §162 statussen
            $table->string('status')->default('geupload'); // aangevraagd|geupload|goedgekeurd|afgekeurd

            $table->text('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['dossier_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
