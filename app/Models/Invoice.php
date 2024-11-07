<?php

namespace App\Models;

use App\Models\Traits\GeneratesPDFFile;
use Attribute;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use GeneratesPDFFile;
    
    protected $fillable = [
        'customer_id',
        'company_id',
        'transaction_date',
        'transaction_ref',
        'order_id',
        'service_purchased',
        'quantity',
        'amount_without_gct',
        'gct',
        'amount_with_gct',
        'currency',
        'pdf_file',
    ];

    /**
     * Define the relationship with the Company model.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Define the relationship with the Customer model.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
