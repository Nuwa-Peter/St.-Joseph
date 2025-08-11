<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DormitoryRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'dormitory_id',
        'room_number',
        'capacity',
    ];

    public function dormitory()
    {
        return $this->belongsTo(Dormitory::class);
    }

    public function occupants()
    {
        return $this->belongsToMany(User::class, 'dormitory_room_user');
    }
}
