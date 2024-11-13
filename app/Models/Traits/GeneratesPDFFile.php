<?php

namespace App\Models\Traits;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

trait GeneratesPDFFile
{
    /**
     * Generates the PDF invoice, saves it to storage and sets the $this->pdf_file property.
     */
    public function generateInvoice(): self
    {
        /*if (! is_null($this->pdf_file)) {
            Storage::delete($this->pdf_file);
        }*/

        $this->pdf_file = $this->transaction_ref . '_invoice.pdf';
        
        Pdf::loadView(view: 'pdf.HN_invoice', data: ['invoice' => $this])
            ->setPaper('a4')
            ->save(filename: $this->pdf_file, disk: env('FILESYSTEM_DISK', 'local'));

        return $this;
    }

    protected function pdfFile(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value) {
                if (is_null(($value))) {
                    $this->generateInvoice();
                }

                return Storage::url($value);
            }
        );
    }

    protected function filename(): Attribute
    {
        $this->appends = array_merge($this->appends, ['filename']);

        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $attributes['transaction_ref'] . '_invoice.pdf';
            },
        );
    }
}