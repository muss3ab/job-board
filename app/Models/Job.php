<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'company_name',
        'salary_min',
        'salary_max',
        'is_remote',
        'job_type',
        'status',
        'published_at',
    ];

    protected $casts = [
        'is_remote' => 'boolean',
        'job_type' => 'string',
        'status' => 'string',
        'published_at' => 'datetime',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
    ];

    // // Define job types as enum-like constants
    public const JOB_TYPE_FULL_TIME = 'full-time';
    public const JOB_TYPE_PART_TIME = 'part-time';
    public const JOB_TYPE_CONTRACT = 'contract';
    public const JOB_TYPE_FREELANCE = 'freelance';

    // // Define status types as enum-like constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    /**
     * Get the languages required for this job.
     */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class);
    }

    /**
     * Get the possible locations for this job.
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class);
    }

    /**
     * Get the categories associated with this job.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get the attribute values for this job.
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(JobAttributeValue::class);
    }
}
