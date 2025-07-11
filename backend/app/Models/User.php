<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * User has many payments.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * User has many payment methods.
     */
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * User has many transactions.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * User has many data subject requests.
     */
    public function dataSubjectRequests()
    {
        return $this->hasMany(DataSubjectRequest::class);
    }

    /**
     * User has many reviews given.
     */
    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * User has many reviews received.
     */
    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    /**
     * User has one reputation.
     */
    public function reputation()
    {
        return $this->hasOne(Reputation::class);
    }

    /**
     * User has many messages.
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * User has many contracts.
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}
