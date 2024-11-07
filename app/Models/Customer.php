<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'tax_id',
        'billing_address',
        'province',
        'email',
        'payment_method',
        'cardholder',
        'card_type',
        'card_last4',
        'card_exp_date'
    ];

    /**
     * Define the relationship with the Invoice model.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
