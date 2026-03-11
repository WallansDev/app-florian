<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const ROLE_SUPPLIER = 'supplier';
    const ROLE_SELLER   = 'seller';
    const ROLE_CLIENT   = 'client';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'is_active',
        'password',
        'api_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function isSupplier(): bool { return $this->role === self::ROLE_SUPPLIER; }
    public function isSeller(): bool   { return $this->role === self::ROLE_SELLER; }
    public function isClient(): bool   { return $this->role === self::ROLE_CLIENT; }

    public function supplier(): HasOne
    {
        return $this->hasOne(Supplier::class);
    }

    public function seller(): HasOne
    {
        return $this->hasOne(Seller::class);
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }
}
