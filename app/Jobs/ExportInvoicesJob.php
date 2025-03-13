<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ExportInvoicesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Carbon $from, public Carbon $to)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Define the filename with a unique identifier
        $filename = 'HN-invoices-' . \Illuminate\Support\Str::uuid() . '.zip';

        // Create a new ZipArchive instance
        $zip = new \ZipArchive();

        // Open the ZIP file for writing
        if ($zip->open($filename, \ZipArchive::CREATE) === true) {

            // Create a temporary file for the CSV
            $csvFilename = tempnam(sys_get_temp_dir(), 'invoices_') . '.csv';
            $csvHandle = fopen($csvFilename, 'w');

            // Write the UTF-8 BOM:
            fputs($csvHandle, "\xEF\xBB\xBF");

            // Write the CSV headers
            fputcsv($csvHandle, [
                'Invoice ID',
                'Customer Name',
                'Reference',
                'Transaction Date',
                'Transaction Ref',
                'Order ID',
                'Service Purchased',
                'Quantity',
                'Amount without GCT',
                'GCT',
                'Amount with GCT',
                'Currency',
                'Customer Email',
                'Customer Tax ID',
                'Billing Address',
                'Province',
                'Payment Method',
                'Cardholder',
                'Card Type',
                'Card Last4',
                'Card Exp Date'
            ]);

            // Query the invoices based on the date range and add them to the ZIP
            Invoice::query()
                ->whereBetween('transaction_date', [$this->from, $this->to])
                ->with('customer') // Eager load the customer relationship
                ->chunk(100, function ($invoices) use ($zip, $csvHandle) {
                    foreach ($invoices as $invoice) {

                        // Write CSV row
                        $dataWithoutGCT = [
                            $invoice->id,
                            $invoice->customer->name,
                            'ref',
                            $invoice->transaction_date,
                            $invoice->transaction_ref,
                            $invoice->order_id,
                            $invoice->service_purchased,
                            $invoice->quantity,
                            $invoice->amount_without_gct,
                            0,
                            0,
                            $invoice->currency,
                            $invoice->customer->email,
                            $invoice->customer->tax_id,
                            $invoice->customer->billing_address,
                            $invoice->customer->province,
                            $invoice->customer->payment_method,
                            $invoice->customer->cardholder,
                            $invoice->customer->card_type,
                            $invoice->customer->card_last4,
                            $invoice->customer->card_exp_date,
                        ];

                        fputcsv($csvHandle, $dataWithoutGCT);

                        $dataGCTOnly = [
                            $invoice->id,
                            $invoice->customer->name,
                            'ref',
                            $invoice->transaction_date,
                            $invoice->transaction_ref,
                            $invoice->order_id,
                            $invoice->service_purchased,
                            $invoice->quantity,
                            0,
                            $invoice->gct,
                            0,
                            $invoice->currency,
                            $invoice->customer->email,
                            $invoice->customer->tax_id,
                            $invoice->customer->billing_address,
                            $invoice->customer->province,
                            $invoice->customer->payment_method,
                            $invoice->customer->cardholder,
                            $invoice->customer->card_type,
                            $invoice->customer->card_last4,
                            $invoice->customer->card_exp_date,
                        ];

                        fputcsv($csvHandle, $dataGCTOnly);

                        $dataWithGCT = [
                            $invoice->id,
                            $invoice->customer->name,
                            'ref',
                            $invoice->transaction_date,
                            $invoice->transaction_ref,
                            $invoice->order_id,
                            $invoice->service_purchased,
                            $invoice->quantity,
                            0,
                            0,
                            $invoice->amount_with_gct,
                            $invoice->currency,
                            $invoice->customer->email,
                            $invoice->customer->tax_id,
                            $invoice->customer->billing_address,
                            $invoice->customer->province,
                            $invoice->customer->payment_method,
                            $invoice->customer->cardholder,
                            $invoice->customer->card_type,
                            $invoice->customer->card_last4,
                            $invoice->customer->card_exp_date,
                        ];

                        fputcsv($csvHandle, $dataWithGCT);

                        // Read the PDF content from storage
                        $contents = Storage::read($invoice->pdf_file);

                        // Add the PDF file to the ZIP archive
                        $zip->addFromString(
                            __('Factura #:id', ['id' => $invoice->id]) . '.pdf',
                            $contents
                        );
                    }
                });

            // Close the CSV file handle
            fclose($csvHandle);

            // Add the CSV file to the ZIP archive
            $zip->addFile($csvFilename, 'invoices.csv');

            // Close the ZIP archive
            $zip->close();

            // Save the ZIP file to storage
            Storage::put($filename, file_get_contents($filename));

            // Send the download notification
            $this->sendDownloadNotification($filename);

            // Delete the temporary files
            unlink($filename);
            unlink($csvFilename);
        } else {
            throw new \Exception('Failed to create ZIP archive.');
        }
    }

    private function sendDownloadNotification(string $filename): void
    {
        Notification::make()
            ->title(__('Facturas exportadas :date', ['date' => now()->format('Y-m-d')]))
            ->body(__('Facturas exportadas con éxito, puedes descargar el zip aquí'))
            ->actions([
                Action::make('Descargar')
                    ->icon('heroicon-o-arrow-down')
                    ->url(route('download.zip', ['file' => $filename]))
                    ->extraAttributes([
                        'target' => '_blank',
                    ]),
            ])
            ->success()
            ->sendToDatabase(users: User::all());
    }
}
