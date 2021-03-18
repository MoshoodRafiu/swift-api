<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'first_name',
        'last_name',
        'other_name',
        'username',
        'email',
        'phone',
        'password',
        'email_verification_token',
        'email_verification_token_expiry'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function adverts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Advert::class);
    }

    public function chat(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Chat::class);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function trades(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function pin(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TransactionPin::class);
    }

    public function verification(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Verification::class);
    }

    public function wallets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function ratings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function rating(): float
    {
        $count = auth()->user()->ratings()->count();
        $sum = auth()->user()->ratings()->sum('star');
        return round($sum / $count, 1);
    }

    public function pinVerified($pin): bool
    {
        return !!Hash::check($pin, auth()->user()->pin()->first()['pin']);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
