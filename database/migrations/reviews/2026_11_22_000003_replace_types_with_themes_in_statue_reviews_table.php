<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The informant's taxonomy (21 Nov 2026) replaces type + iconic flag with one or more themes.
 * Production applies the same change through Supabase migrations; keep the two in sync.
 */
return new class extends Migration
{
    protected $connection = 'reviews';

    public function up(): void
    {
        Schema::connection($this->connection)->table('statue_reviews', function (Blueprint $table) {
            $table->json('themes')->nullable()->after('statue_name');
        });

        Schema::connection($this->connection)->table('statue_reviews', function (Blueprint $table) {
            $table->dropColumn(['statue_type', 'is_iconic']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('statue_reviews', function (Blueprint $table) {
            $table->string('statue_type', 20)->nullable();
            $table->boolean('is_iconic')->default(false);
            $table->dropColumn('themes');
        });
    }
};
