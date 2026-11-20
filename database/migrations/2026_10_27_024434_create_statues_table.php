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
            $table->foreignId('statue_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('proposed_type_id')->nullable()->constrained('statue_types')->nullOnDelete();
            $table->boolean('proposed_iconic')->default(false);
            $table->foreignId('junction_type_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('junction_arms')->nullable();
            $table->json('description')->nullable();
            $table->json('sources')->nullable();
            $table->json('photos')->nullable();
            $table->string('photo_credit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statues');
    }
};
