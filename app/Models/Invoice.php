<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'amount_paid',
        'due_date',
        'status',
        'academic_year',
        'term',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * Get the student (user) that this invoice belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the items included in this invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the payments made towards this invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Calculate the outstanding balance for the invoice.
     */
    public function balance(): Attribute
    {
        return new Attribute(
            get: fn () => $this->total_amount - $this->amount_paid,
        );
    }
}
