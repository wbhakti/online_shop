<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAddress extends Model
{
    use SoftDeletes;

    protected $table = 'customer_addresses';

    protected $fillable = [
        'customer_id',
        'label',
        'recipient_name',
        'recipient_phone',
    
        'province_id',
        'city_id',
        'district_id',
        'subdistrict_id',
    
        'province',
        'city',
        'district',
        'village',
    
        'postal_code',
        'address',
        'notes',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_default' => 'boolean',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}