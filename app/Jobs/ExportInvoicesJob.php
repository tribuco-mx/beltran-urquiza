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

            $firstInvoice = Invoice::query()
                ->whereBetween('transaction_date', [$this->from, $this->to])
                ->first();

            fputcsv($csvHandle, ['Nombre', $firstInvoice?->company->name ?? env('COMPANY_NAME')]);
            fputcsv($csvHandle, ['RTN', $firstInvoice?->company->tax_id ?? env('COMPANY_TAX_ID')]);

            // Write the CSV headers
            fputcsv($csvHandle, [
                'ID',
                'Tipo',
                'Fecha',
                'Referencia',
                'Concepto',
                'Observaciones',
                'Cuenta',
                'Billed To',
                'Tax ID',
                'Concepto (Detalle)',
                'Debe',
                'Haber',
                'Debe2',
                'Haber2',
                'Centro Costo',
                'Centro Costo 2',
                'Centro Costo 3',
                'Cancelada',
            ]);

            // Query the invoices based on the date range and add them to the ZIP
            Invoice::query()
                ->whereBetween('transaction_date', [$this->from, $this->to])
                ->with('customer') // Eager load the customer relationship
                ->chunk(200, function ($invoices) use ($zip, $csvHandle) {
                    $index = 1;
                    foreach ($invoices as $invoice) {
                        $dataWithGct = [
                            $index,
                            'Diario',
                            $invoice->transaction_date,
                            $invoice->formattedInvoiceNumber,
                            '',
                            $invoice->transaction_ref,
                            '4013004',
                            $invoice->customer?->name ?? '',
                            $invoice->customer?->tax_id ?? '',
                            $invoice->order_id . ' - ' . __($invoice->service_purchased),
                            '',
                            $invoice->amount_without_gct,
                            '',
                            '',
                            'VAS0000',
                            '',
                            '',
                            $invoice->is_cancelled ? 'Cancelada' : '',
                        ];


                        fputcsv($csvHandle, $dataWithGct);
                        $dataWithoutGCT = [
                            $index,
                            'Diario',
                            $invoice->transaction_date,
                            $invoice->formattedInvoiceNumber,
                            '',
                            $invoice->transaction_ref,
                            '2124902',
                            $invoice->customer?->name ?? '',
                            $invoice->customer?->tax_id ?? '',
                            $invoice->order_id . ' - ' . __($invoice->service_purchased),
                            '',
                            $invoice->gct,
                            '',
                            '',
                            'VAS0000',
                            '',
                            '',
                            $invoice->is_cancelled ? 'Cancelada' : '',
                        ];

                        fputcsv($csvHandle, $dataWithoutGCT);

                        $dataOnlyGct = [
                            $index,
                            'Diario',
                            $invoice->transaction_date,
                            $invoice->formattedInvoiceNumber,
                            '',
                            $invoice->transaction_ref,
                            '1110601',
                            $invoice->customer?->name ?? '',
                            $invoice->customer?->tax_id ?? '',
                            $invoice->order_id . ' - ' . __($invoice->service_purchased),
                            $invoice->amount_with_gct,
                            '',
                            '',
                            '',
                            'VAS0000',
                            '',
                            '',
                            $invoice->is_cancelled ? 'Cancelada' : '',
                        ];

                        fputcsv($csvHandle, $dataOnlyGct);

                        $index++;

                        // Read the PDF content from storage
                        $contents = Storage::read($invoice->pdf_file);

                        // Add the PDF file to the ZIP archive
                        $zip->addFromString(
                            __('Factura #:id', ['id' => $invoice->folio]) . '.pdf',
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
