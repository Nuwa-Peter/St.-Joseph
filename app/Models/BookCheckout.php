<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCheckout extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'checkout_date',
        'due_date',
        'returned_date',
        'status',
    ];

    protected $casts = [
        'checkout_date' => 'datetime',
        'due_date' => 'datetime',
        'returned_date' => 'datetime',
    ];

    /**
     * Get the book that was checked out.
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the user (student) who checked out the book.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
