<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('statues', function (Blueprint $table) {
            $table->string('status', 12)->default('published')->index()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('statues', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
