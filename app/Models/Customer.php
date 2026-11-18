<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\CustomerAddress;

class Customer extends Authenticatable
{
    use SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'customer_code',
        'name',
        'email',
        'phone',
        'password',
        'photo',
        'birth_date',
        'gender',
        'email_verified_at',
        'phone_verified_at',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function transactions()
    {
        return $this->hasMany(
            Transaction::class,
            'customer_id',
            'id'
        );
    }

    public function addresses()
    {
        return $this->hasMany(
            CustomerAddress::class,
            'customer_id',
            'id'
        );
    }

    public function defaultAddress()
    {
        return $this->hasOne(
            CustomerAddress::class,
            'customer_id',
            'id'
        )->where('is_default', true);
    }
}