<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Volledige snapshots van wat de calculator op moment van aanvraag uitspuugde.
        // Geen partial data: zo kun je later precies reproduceren wat de klant zag.
        Schema::table('leads', function (Blueprint $table) {
            $table->json('rdw_snapshot_json')->nullable()->after('autonomia');
            $table->json('bpm_calculation_json')->nullable()->after('rdw_snapshot_json');
            $table->json('import_calculation_json')->nullable()->after('bpm_calculation_json');
        });

        Schema::table('dossiers', function (Blueprint $table) {
            // rdw_data_json bestaat al — voeg de calculatie-snapshots toe.
            $table->json('bpm_calculation_json')->nullable()->after('bpm_indicatie_eur');
            $table->json('import_calculation_json')->nullable()->after('bpm_calculation_json');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['rdw_snapshot_json', 'bpm_calculation_json', 'import_calculation_json']);
        });

        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn(['bpm_calculation_json', 'import_calculation_json']);
        });
    }
};
