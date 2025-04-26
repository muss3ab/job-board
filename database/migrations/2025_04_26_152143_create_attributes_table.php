<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Attribute;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', [
                Attribute::TYPE_TEXT,
                Attribute::TYPE_NUMBER,
                Attribute::TYPE_BOOLEAN,
                Attribute::TYPE_DATE,
                Attribute::TYPE_SELECT
            ]);
            $table->json('options')->nullable(); // For select type
            $table->timestamps();
        });

        Schema::create('job_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained()->onDelete('cascade');
            $table->text('value');
            $table->timestamps();

            // Add index for faster EAV queries
            $table->index(['job_id', 'attribute_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('job_attribute_values');
    }
};
