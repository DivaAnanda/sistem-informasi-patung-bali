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
        Schema::create('statues', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('source_no')->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->foreignId('regency_id')->constrained()->restrictOnDelete();
            $table->string('district');
            $table->string('village')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->json('proposed_themes')->nullable();
            $table->string('proposal_note', 300)->nullable();
            $table->foreignId('junction_type_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('junction_arms')->nullable();
            $table->json('description')->nullable();
            $table->json('sources')->nullable();
            $table->json('photos')->nullable();
            $table->string('photo_credit')->nullable();
            $table->timestamps();
        });

        Schema::create('statue_theme', function (Blueprint $table) {
            $table->foreignId('statue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->primary(['statue_id', 'theme_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statue_theme');
        Schema::dropIfExists('statues');
    }
};
