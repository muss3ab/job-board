<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Job;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('company_name');
            $table->decimal('salary_min', 10, 2);
            $table->decimal('salary_max', 10, 2);
            $table->boolean('is_remote')->default(false);
            $table->enum('job_type', [
                Job::JOB_TYPE_FULL_TIME,
                Job::JOB_TYPE_PART_TIME,
                Job::JOB_TYPE_CONTRACT,
                Job::JOB_TYPE_FREELANCE
            ]);
            $table->enum('status', [
                Job::STATUS_DRAFT,
                Job::STATUS_PUBLISHED,
                Job::STATUS_ARCHIVED
            ])->default(Job::STATUS_DRAFT);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country');
            $table->timestamps();

            // Add index for faster location searches
            $table->index(['city', 'country']);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Pivot tables for many-to-many relationships
        Schema::create('job_language', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['job_id', 'language_id']);
        });

        Schema::create('job_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['job_id', 'location_id']);
        });

        Schema::create('category_job', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['job_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('job_language');
        Schema::dropIfExists('job_location');
        Schema::dropIfExists('category_job');

    }
};
