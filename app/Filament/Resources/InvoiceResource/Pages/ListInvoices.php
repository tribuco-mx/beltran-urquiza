<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Imports\InvoiceImporter;
use App\Filament\Resources\InvoiceResource;
use App\Jobs\ExportInvoicesJob;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->importer(importer: InvoiceImporter::class)
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
