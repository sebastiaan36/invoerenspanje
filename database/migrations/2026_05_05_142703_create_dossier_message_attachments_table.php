<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossier_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_message_id')
                ->constrained('dossier_messages')
                ->cascadeOnDelete();
            $table->string('filename');
            $table->string('path'); // op `local` disk (private), niet via webroot
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();

            $table->index('dossier_message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_message_attachments');
    }
};
