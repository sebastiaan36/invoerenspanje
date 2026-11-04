<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hacienda "precios medios de venta" (Anexo I van de jaarlijkse Orden HAC).
     * Gevuld door `hacienda:sync` vanuit database/data/hacienda/*.csv.
     */
    public function up(): void
    {
        Schema::create('hacienda_vehicle_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('table_year');
            $table->string('brand_key', 60);
            $table->string('brand', 80);
            $table->string('model', 255);
            $table->unsignedSmallInteger('start_year');
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->unsignedSmallInteger('cc')->nullable();
            $table->string('fuel', 8);
            $table->unsignedSmallInteger('kw');
            $table->unsignedSmallInteger('cv')->nullable();
            $table->unsignedInteger('price_eur');

            $table->index(['table_year', 'brand_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hacienda_vehicle_prices');
    }
};
