<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    const STATUS_PENDING  = 'pending';
    const STATUS_PAID     = 'paid';
    const STATUS_LATE     = 'late';
    const STATUS_DISPUTED = 'disputed';

    protected $fillable = [
        'order_id',
        'payer_id',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }
}
