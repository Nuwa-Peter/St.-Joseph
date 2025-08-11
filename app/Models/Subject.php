<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    public function streams(): BelongsToMany
    {
        return $this->belongsToMany(Stream::class);
    }

    public function papers(): HasMany
    {
        return $this->hasMany(Paper::class);
    }
}
