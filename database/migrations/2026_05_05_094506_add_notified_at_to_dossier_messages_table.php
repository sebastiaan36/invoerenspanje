<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossier_messages', function (Blueprint $table) {
            // Wanneer dit bericht is meegestuurd in een notification-mail.
            // null = nog niet gemaild → wordt opgepakt door de eerstvolgende digest.
            $table->timestamp('notified_at')->nullable()->after('read_at');
        });
    }

    public function down(): void
    {
        Schema::table('dossier_messages', function (Blueprint $table) {
            $table->dropColumn('notified_at');
        });
    }
};
