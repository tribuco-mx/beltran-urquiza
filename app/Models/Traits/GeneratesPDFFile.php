<?php

namespace App\Models\Traits;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

trait GeneratesPDFFile
{
    public function regeneratePDF(): self
    {
        $this->is_cancelled
            ? $this->generateCancelledInvoice()
            : $this->generateInvoice();
        return $this;
    }

    /**
     * Generates the PDF invoice, saves it to storage and sets the $this->pdf_file property.
     */
    public function generateInvoice(): self
    {
        /*if (! is_null($this->pdf_file)) {
            Storage::delete($this->pdf_file);
        }*/

        $this->pdf_file = $this->transaction_ref . '_invoice.pdf';

        Pdf::loadView('pdf.HN_invoice', ['invoice' => $this])
            ->setPaper('a4')
            ->save(filename: $this->pdf_file, disk: env('FILESYSTEM_DISK', 'local'));

        return $this;
    }

    public function generateCancelledInvoice(): self
    {
        $this->pdf_file = 'cancelada_' . $this->transaction_ref . '_invoice.pdf';

        Pdf::loadView('pdf.cancelled_invoice', ['invoice' => $this])
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
