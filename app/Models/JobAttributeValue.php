<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class JobAttributeValue extends Model
{
    use HasFactory;
    protected $fillable = ['job_id', 'attribute_id', 'value'];

    
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    
    public function getValueAttribute($value)
    {
        if (!$this->relationLoaded('attribute')) {
            $this->load('attribute');
        }

        switch ($this->attribute->type) {
            case Attribute::TYPE_NUMBER:
                return (float) $value;
            case Attribute::TYPE_BOOLEAN:
                return (bool) $value;
            case Attribute::TYPE_DATE:
                return \Carbon\Carbon::parse($value);
            default:
                return $value;
        }
    }

}
