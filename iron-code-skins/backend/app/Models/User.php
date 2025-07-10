<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'kyc_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function kycDocuments()
    {
        return $this->hasMany(KYCDocument::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}