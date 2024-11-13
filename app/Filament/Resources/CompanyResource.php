<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    public static ?int $navigationSort = 1;

    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tax_id')
                    ->label('RTN')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Phone')
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->label('Address')
                    ->maxLength(255),
                Forms\Components\TextInput::make('city')
                    ->label('City')
                    ->maxLength(255),
                Forms\Components\TextInput::make('state')
                    ->label('State')
                    ->maxLength(255),
                Forms\Components\TextInput::make('logo')
                    ->label('Logo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('website')
                    ->label('Website')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax_id')
                    ->label(__('RTN'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->label(__('Address'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->label(__('City'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('state')
                    ->label(__('State'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('logo')
                    ->label(__('Logo'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('website')
                    ->label(__('Website'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
