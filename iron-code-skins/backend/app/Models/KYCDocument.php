<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KYCDocument extends Model
{
    use HasFactory;

    protected $table = 'kyc_documents';

    protected $fillable = [
        'user_id',
        'document_type',
        'document_data',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}