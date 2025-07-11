<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reputation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_reviews',
        'average_score',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'average_score' => 'decimal:2',
    ];

    /**
     * The user this reputation belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
