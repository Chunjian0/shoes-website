<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'type',
        'contact_person',
        'contact_phone',
        'line1',
        'line2',
        'city',
        'postcode',
        'state',
        'country',
        'is_default_billing',
        'is_default_shipping',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getFormattedAddressAttribute()
    {
        $parts = [
            $this->line1,
            $this->line2,
            $this->postcode . ' ' . $this->city,
            $this->state,
            $this->country,
        ];
        return implode(', ', array_filter($parts));
    }
}
