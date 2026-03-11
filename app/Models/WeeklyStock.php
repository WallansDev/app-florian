<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklyStock extends Model
{
    protected $fillable = [
        'supplier_id',
        'week_start',
        'total_qty',
        'available_qty',
        'unit_price',
    ];

    protected $casts = [
        'week_start'    => 'date',
        'unit_price'    => 'decimal:2',
        'total_qty'     => 'integer',
        'available_qty' => 'integer',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function sellerAllocations(): HasMany
    {
        return $this->hasMany(SellerAllocation::class);
    }

    public function getTotalAllocatedAttribute(): int
    {
        return $this->sellerAllocations()->sum('allocated_qty');
    }
}
