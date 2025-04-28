<?php

namespace App\Models;

use App\Models\Traits\GeneratesPDFFile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'is_cancelled',
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function(Invoice $model) {
            /** @var int $limit */
            $limit = (int) env('INVOICE_LIMIT', 10000);

            if ( Invoice::query()->count() >= 1 && Invoice::latest()->first()->id >= $limit) {
                throw new \Exception("Invoice limit of {$limit} reached");
            }
            if (now() > Carbon::parse(env('EMISSION_LIMIT_DATE', '10/12/2025'))) {
                throw new \Exception("Emission limit date reached");
            }
        });
    }

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

    protected function formattedInvoiceNumber(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => preg_replace('/(\d{3})(\d{3})(\d{2})(\d{8})/', '$1-$2-$3-$4', sprintf('00000101%08d', $this->id)),
        );
    }
}
