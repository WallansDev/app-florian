<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    const STATUS_PENDING    = 'pending';
    const STATUS_CONFIRMED  = 'confirmed';
    const STATUS_DELIVERED  = 'delivered';
    const STATUS_CANCELLED  = 'cancelled';

    protected $fillable = [
        'order_number',
        'buyer_id',
        'seller_id',
        'supplier_id',
        'week_start',
        'quantity',
        'unit_price',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'week_start'   => 'date',
        'unit_price'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'quantity'     => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Order $order) {
            $order->total_amount = $order->quantity * $order->unit_price;
        });
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
