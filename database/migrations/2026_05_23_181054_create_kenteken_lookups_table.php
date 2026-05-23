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
        Schema::create('kenteken_lookups', function (Blueprint $table) {
            $table->id();
            $table->string('kenteken', 20);
            $table->string('merk')->nullable();
            $table->string('model')->nullable();
            $table->unsignedSmallInteger('bouwjaar')->nullable();
            $table->string('brandstof')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('page')->nullable();
            $table->timestamps();

            $table->index('kenteken');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kenteken_lookups');
    }
};
