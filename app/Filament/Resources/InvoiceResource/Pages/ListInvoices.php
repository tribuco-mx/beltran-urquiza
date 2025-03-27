<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Imports\InvoiceImporter;
use App\Filament\Resources\InvoiceResource;
use App\Jobs\ExportInvoicesJob;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('combine_csv')
                ->label('Combine CSVs')
                ->modalSubmitActionLabel(__('Combine'))
                ->translateLabel()
                ->form([
                    Forms\Components\FileUpload::make('csv_files')
                        ->label('Upload CSV Files')
                        ->translateLabel()
                        ->multiple()
                        ->disk('s3') // or your chosen disk
                        ->directory('csv-uploads')
                        ->required(),
                ])->action(function (array $data) {
                    $combinedData = [];
                    $header = null;
                    $totalRows = 0;
                    $filesProcessed = 0;

                    foreach ($data['csv_files'] as $filePath) {
                        $fullPath = Storage::disk('s3')->url($filePath);
                        $file = Storage::disk('s3')->get($filePath);

                        if (empty($file)) {
                            Notification::make()
                                ->title("File not found: {$filePath}")
                                ->danger()
                                ->send();
                            continue;
                        }
                        $filesProcessed++;
                        // Skip the first 4 rows (the header offset)
                        $fileRows = array_map('str_getcsv', explode("\n", $file));
                        $skipped = array_splice($fileRows, 0, 4);
                        if (empty($skipped)) {
                            Notification::make()
                                ->title("File {$filePath} has less than 5 rows.")
                                ->danger()
                                ->send();
                            continue;
                        }

                        // Read header row from the 5th line
                        $currentHeader = array_shift($fileRows);
                        if (empty($currentHeader)) {
                            Notification::make()
                                ->title("No header found in file: {$filePath}")
                                ->danger()
                                ->send();
                            continue;
                        }

                        // Use the first file's header as standard
                        if (is_null($header)) {
                            $header = $currentHeader;
                            $combinedData[] = $header;
                        }

                        // Process the rest of the rows
                        $combinedData = array_merge($combinedData, $fileRows);
                        $totalRows += count($fileRows);
                    }

                    if (empty($combinedData)) {
                        Notification::make()
                            ->title('No data found to combine. Files processed: ' . $filesProcessed)
                            ->danger()
                            ->send();
                        return;
                    }

                    $combinedData = implode("\n", array_map('implode', $combinedData));

                    Notification::make()
                        ->title(__('CSV files combined successfully.'))
                        ->body("Header: " . implode(', ', $header) . " | Total de filas: $totalRows")
                        ->warning()
                        ->send();

                    // Write the combined data to a new CSV file with a 4-row header offset
                    $combinedFilePath = 'combined-' . date('YmdHis') . '.csv';
                    Storage::disk('s3')->put($combinedFilePath, $combinedData);

                    Notification::make()
                        ->title('CSV files combined successfully.')
                        ->body('Download the combined CSV.')
                        ->success()
                        ->actions([
                            Action::make('Descargar')
                                ->icon('heroicon-o-arrow-down')
                                ->url(Storage::disk('s3')->url($combinedFilePath))
                                ->extraAttributes([
                                    'target' => '_blank',
                                ]),
                        ])
                        ->sendToDatabase(users: User::all())
                        ->send();
                }),
            Actions\ImportAction::make()
                ->importer(importer: InvoiceImporter::class)
                ->chunkSize(5000)
                ->maxRows(100000)
                ->headerOffset(4),
            Actions\CreateAction::make(),
            Actions\Action::make('export_invoices')
                ->label('Export Invoices')
                ->icon('heroicon-s-cloud-arrow-down')
                ->form(form: [
                    Forms\Components\DatePicker::make('start_date')
                        ->required(),
                    Forms\Components\DatePicker::make('end_date')
                        ->required(),
                ])
                ->action(function (array $data) {
                    ExportInvoicesJob::dispatch(
                        \Carbon\Carbon::parse($data['start_date']),
                        \Carbon\Carbon::parse($data['end_date']),
                    );

                    Notification::make()
                        ->info()
                        ->title('Exporing Invoices...')
                        ->send();
                }),
        ];
    }
}
