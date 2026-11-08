<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Moment waarop de status op 'verloren' is gezet; startpunt van de 2 weken tot de feedbackmail.
            $table->timestamp('lost_at')->nullable()->after('status');
            $table->string('feedback_token', 64)->nullable()->unique()->after('lost_at');
            $table->timestamp('feedback_requested_at')->nullable()->after('feedback_token');
        });

        // Bestaande verloren leads: de laatste wijziging is de beste benadering van
        // het moment waarop ze verloren zijn. Wie al langer dan 2 weken verloren
        // staat, krijgt de mail bij de eerstvolgende run.
        DB::table('leads')
            ->where('status', 'verloren')
            ->update(['lost_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropUnique(['feedback_token']);
            $table->dropColumn(['lost_at', 'feedback_token', 'feedback_requested_at']);
        });
    }
};
