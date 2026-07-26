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
        'folio'
    ];

    protected static function boot()
    {
        parent::boot();

        self::creating(function (Invoice $model) {
            $limit = (int) env('INVOICE_LIMIT', 40000);
            $firstFolio = (int) env('FIRST_FOLIO', 1);

            if (Invoice::query()->count() >= 1 && Invoice::latest()->first()->id >= $limit) {
                throw new \Exception("Invoice limit of {$limit} reached");
            }

            if (now()->greaterThan(Carbon::parse('2027-07-14'))) {
                throw new \Exception('Emission limit date reached');
            }

            $lastInvoice = Invoice::whereNotNull('folio')
                ->orderByDesc('folio')
                ->first();

            $nextFolio = $lastInvoice ? ((int) $lastInvoice->folio + 1) : $firstFolio;

            $model->folio = max($nextFolio, $firstFolio);
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
            get: fn (mixed $value) => preg_replace('/(\d{3})(\d{3})(\d{2})(\d{8})/', '$1-$2-$3-$4', sprintf('00000101%08d', $this->folio)),
        );
    }
}
