<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = ['user_id', 'company', 'address', 'siret'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sellers(): HasMany
    {
        return $this->hasMany(Seller::class);
    }

    public function weeklyStocks(): HasMany
    {
        return $this->hasMany(WeeklyStock::class);
    }
}
