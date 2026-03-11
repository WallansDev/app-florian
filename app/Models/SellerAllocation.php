<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerAllocation extends Model
{
    protected $fillable = [
        'weekly_stock_id',
        'seller_id',
        'allocated_qty',
        'remaining_qty',
    ];

    protected $casts = [
        'allocated_qty' => 'integer',
        'remaining_qty' => 'integer',
    ];

    public function weeklyStock(): BelongsTo
    {
        return $this->belongsTo(WeeklyStock::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
