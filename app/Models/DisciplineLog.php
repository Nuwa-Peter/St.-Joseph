<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplineLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recorded_by_id',
        'type',
        'log_date',
        'description',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    /**
     * Get the student associated with the discipline log.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the staff member who recorded the log.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }
}
