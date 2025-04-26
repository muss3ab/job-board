<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Language extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Get the jobs that require this language.
     */
    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class);
    }

}
