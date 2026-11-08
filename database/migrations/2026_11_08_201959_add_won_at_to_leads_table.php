<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the moment a lead was won, so revenue lands in the month it was won
     * instead of the month the lead came in.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('won_at')->nullable()->after('status');
            $table->index('won_at');
        });

        // Existing won leads: the last change is the best approximation of the moment they were won.
        DB::table('leads')
            ->where('status', 'gewonnen')
            ->update(['won_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['won_at']);
            $table->dropColumn('won_at');
        });
    }
};
