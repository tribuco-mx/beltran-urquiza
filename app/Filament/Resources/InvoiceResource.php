<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Mail\InvoiceProcessed;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Mail;

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
                        Forms\Components\TextInput::make('order_id')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('customer_id')
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
                Tables\Columns\TextColumn::make('customer.name')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_ref')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service_purchased')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_without_gct')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gct')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_with_gct')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
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
                Tables\Actions\Action::make('send_email')
                    ->label('Send email')
                    ->icon('heroicon-o-paper-airplane')
                    ->action(function (Invoice $record) {
                        $record->generateInvoice();

                        Mail::to($record->customer->email)->send(new InvoiceProcessed(invoice: $record));
                    })
                    ->after(callback: fn() => Notification::make()->success()->title('Invoice sent successfully')->send()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
}
