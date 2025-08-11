<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'dormitory_room_id',
        'user_id',
        'academic_year',
    ];

    /**
     * Get the room for the assignment.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(DormitoryRoom::class, 'dormitory_room_id');
    }

    /**
     * Get the student for the assignment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
