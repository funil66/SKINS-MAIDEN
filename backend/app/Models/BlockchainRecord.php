<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockchainRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'reference_id',
        'blockchain',
        'tx_hash',
        'status',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
