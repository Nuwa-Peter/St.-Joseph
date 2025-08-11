<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_category_id',
        'user_id',
        'amount',
        'expense_date',
        'description',
        'receipt_number',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    /**
     * Get the category that this expense belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * Get the user (staff) who recorded this expense.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
