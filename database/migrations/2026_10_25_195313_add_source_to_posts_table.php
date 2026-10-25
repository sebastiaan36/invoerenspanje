<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Posts written as Markdown files in resources/content/blog are synced
     * into this table by `php artisan blog:sync`; `source = 'file'` marks
     * them so the admin can keep them read-only.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('source')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
