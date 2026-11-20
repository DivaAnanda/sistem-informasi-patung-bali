<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lives on the "reviews" connection (Supabase in production) so that rebuilding the statue
 * database from the CSV never touches the expert's work. Production applies the same schema
 * through Supabase migrations; keep the two in sync.
 */
return new class extends Migration
{
    protected $connection = 'reviews';

    public function up(): void
    {
        Schema::connection($this->connection)->create('statue_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('source_no')->unique();
            $table->string('statue_type', 20)->nullable();
            $table->boolean('is_iconic')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('statue_reviews');
    }
};
