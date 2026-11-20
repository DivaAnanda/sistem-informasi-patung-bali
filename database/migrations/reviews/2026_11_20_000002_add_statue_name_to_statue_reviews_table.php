<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Production applies the same change through Supabase migrations; keep the two in sync.
 */
return new class extends Migration
{
    protected $connection = 'reviews';

    public function up(): void
    {
        Schema::connection($this->connection)->table('statue_reviews', function (Blueprint $table) {
            $table->string('statue_name', 150)->nullable()->after('source_no');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('statue_reviews', function (Blueprint $table) {
            $table->dropColumn('statue_name');
        });
    }
};
