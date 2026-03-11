<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    protected $fillable = ['user_id', 'supplier_id', 'unit_price'];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(SellerAllocation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
