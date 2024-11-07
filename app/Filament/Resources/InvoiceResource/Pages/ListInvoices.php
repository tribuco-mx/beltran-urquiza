<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Imports\InvoiceImporter;
use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
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
        ];
    }
}
