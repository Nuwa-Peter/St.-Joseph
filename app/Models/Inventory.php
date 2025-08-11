<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'quantity',
        'condition',
        'location',
        'purchase_date',
        'purchase_price',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];
}
