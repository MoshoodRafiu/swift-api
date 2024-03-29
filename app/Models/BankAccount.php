<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function advert(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Advert::class);
    }
}
