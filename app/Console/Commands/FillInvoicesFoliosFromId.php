<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class FillInvoicesFoliosFromId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-invoices-folios-from-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill folio field from invoices using their ID, there was no folio field and the ids where used to determine a consecutive identifier, but due to some errors there was an offset and a folio is now required';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoices = Invoice::all();

        foreach ($invoices as $invoice) {
            $invoice->folio = $invoice->id;
            $invoice->save();
        }
    }
}
