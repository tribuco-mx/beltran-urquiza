<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Mail\InvoiceProcessed;
use App\Models\Invoice;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Svg\Tag\Text;

class InvoiceResource extends Resource
{
    public static ?int $navigationSort = 0;

    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Información de la factura'))
                    ->schema([
                        Forms\Components\TextInput::make('folio')
                            ->label('Folio')
                            ->unique(),
                        Forms\Components\TextInput::make('order_id')
                            ->label('Tax invoice #')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('customer_id')
                            ->label('Billed To')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->relationship('customer', 'name'),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->required(),
                        Forms\Components\TextInput::make('transaction_ref')
                            ->required(),
                        Forms\Components\Select::make('company_id')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->relationship('company', 'name')
                            ->columnSpanFull(),
                        Forms\Components\Fieldset::make('Detalles de la factura')
                            ->schema([
                                Forms\Components\TextInput::make('service_purchased')
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('amount_without_gct')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('gct')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('amount_with_gct')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('currency')
                                    ->required(),
                            ])
                            ->columns(6)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Factura')
                    ->sortable(),
                Tables\Columns\TextColumn::make('folio')
                    ->label('Folio')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_cancelled')
                    ->label('Cancelada')
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->getStateUsing(fn(Invoice $record): ?string => $record->is_cancelled ? 'Cancelada' : '')
                    ->color(Color::Red),
                Tables\Columns\TextColumn::make('customer.name')
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_ref')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service_purchased')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_without_gct')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('gct')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_with_gct')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Regenerate PDF')
                    ->label('Regenerar Factura')
                    ->icon('heroicon-o-document-text')
                    ->action(function (Invoice $record) {
                        $record->generateInvoice()->save();
                        Storage::download($record->pdf_file);
                    })
                    ->after(callback: fn() => Notification::make()->success()->title('Factura regenerada con éxito')->send()),
                Tables\Actions\Action::make('send_email')
                    ->label('Send email')
                    ->icon('heroicon-o-paper-airplane')
                    ->action(function (Invoice $record) {
                        $record->generateInvoice()->save();

                        try {
                            Mail::to($record->customer->email)->send(new InvoiceProcessed(invoice: $record));
                            Log::info('Email sent');
                        } catch (\Exception $exception) {
                            dd($exception);
                        }
                    })
                    ->after(callback: fn() => Notification::make()->success()->title('Invoice sent successfully')->send()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                    static::getCancelActions(Tables\Actions\BulkAction::make('cancel')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getCancelActions(Tables\Actions\BulkAction $action): Tables\Actions\BulkAction
    {
        return $action
            ->requiresConfirmation()
            ->label('Cancel invoices')
            ->translateLabel()
            ->icon('heroicon-o-x-circle')
            ->color(Color::Red)
            ->action(function (Collection $records) {
                foreach ($records as $record) {
                    $record->update(['is_cancelled' => true]);
                    $record->save();

                    $record->generateCancelledInvoice()->save();

                    Notification::make()->success()->title('Invoices cancelled successfully')->send();
                }

                self::generateZip($records);
            });
    }

    public static function generateZip(Collection $invoices): void
    {
        // Define the filename with a unique identifier
        $filename = 'facturas-canceladas-' . \Illuminate\Support\Str::uuid() . '.zip';

        // Create a new ZipArchive instance
        $zip = new \ZipArchive();

        // Open the ZIP file for writing
        if ($zip->open($filename, \ZipArchive::CREATE) === true) {
            foreach ($invoices as $invoice) {
                // Read the PDF content from storage
                $contents = Storage::read($invoice->pdf_file);

                // Add the PDF file to the ZIP archive
                $zip->addFromString(
                    __('Factura #:id cancelada', ['id' => $invoice->id]) . '.pdf',
                    $contents
                );
            };
            // Close the ZIP archive
            $zip->close();

            // Save the ZIP file to storage
            Storage::put($filename, file_get_contents($filename));

            // Send the download notification
            Notification::make()
                ->title(__('Facturas canceladas :date', ['date' => now()->format('Y-m-d')]))
                ->body(__('Facturas canceladas con éxito, puedes descargar el zip con las facturas aquí'))
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

            // Delete the temporary files
            unlink($filename);
        } else {
            throw new \Exception('Failed to create ZIP archive.');
        }
    }
}
