<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'quantity',
        'available_quantity',
        'shelf_location',
        'published_date',
    ];

    /**
     * Get the checkouts for the book.
     */
    public function checkouts()
    {
        return $this->hasMany(BookCheckout::class);
    }
}
