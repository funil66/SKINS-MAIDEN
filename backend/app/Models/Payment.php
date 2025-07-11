<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'contract_id',
        'amount',
        'currency',
        'payment_gateway',
        'gateway_payment_id',
        'status',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationships to eager load by default.
     */
    protected $with = ['user', 'contract'];

    /**
     * Payment belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Payment belongs to a contract.
     */
    public function contract()
    {
        return $this->belongsTo(App\Models\Contract::class);
    }

    /**
     * Payment has many transactions.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
