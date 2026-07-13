<?php

namespace App\Filament\Imports;

use App\Filament\Resources\CompanyResource;
use App\Mail\InvoiceProcessed;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

ini_set('max_execution_time', 10000);


class InvoiceImporter extends Importer
{
    protected static ?string $model = Invoice::class;

    public static function getColumns(): array
    {
        return [
            // Invoice fields directly mapped
            ImportColumn::make('transaction_date')
                ->label('Transaction Date')
                ->fillRecordUsing(function (Invoice $record, array $data) {

                    $value = trim($data['transaction_date'] ?? '');

                    if (empty($value)) {
                        $record->transaction_date = now();
                        return;
                    }

                    try {
                        $normalized = str_replace(['-', '.'], '/', $value);

                        $normalized = explode(' ', $normalized)[0];

                        $date = \Carbon\Carbon::createFromFormat('d/m/Y', $normalized);

                        $record->transaction_date = $date->format('Y-m-d');
                    } catch (\Exception $e) {
                        try {
                            $record->transaction_date = \Carbon\Carbon::parse($value)->format('Y-m-d');
                        } catch (\Exception $e) {
                            Log::error('Error parsing transaction date: ' . $value);
                            return;
                        }
                    }
                }),

            ImportColumn::make('transaction_ref')
                ->label('Transaction Ref. #')
                ->fillRecordUsing(function (Invoice $record, array $data) {
                    $record->transaction_ref = $data['transaction_ref'] ?? '';
                }),

            ImportColumn::make('tax_invoice')
                ->label('Tax Invoice #')
                ->fillRecordUsing(function (Invoice $record, array $data) {
                    $record->order_id = $data['tax_invoice'] ?? '';
                }),

            ImportColumn::make('service_purchased')
                ->label('Service(s) Purchased')
                ->fillRecordUsing(function (Invoice $record, array $data) {
                    $record->service_purchased = $data['service_purchased'] ?? '';
                }),


            ImportColumn::make('quantity')
                ->label('Quantity')
                ->fillRecordUsing(function (Invoice $record, array $data) {
                    $record->quantity = intval($data['quantity'] ?? 0);
                }),

            ImportColumn::make('amount_without_gct')
                ->label('Amount w/out GCT')
                ->fillRecordUsing(function (Invoice $record, array $data) {
                    $record->amount_without_gct = static::castToFloat($data['amount_without_gct'] ?? 0);
                }),

            ImportColumn::make('gct')
                ->label('GCT')
                ->fillRecordUsing(function (Invoice $record, array $data) {
                    $record->gct = static::castToFloat($data['gct'] ?? 0);
                }),

            ImportColumn::make('amount_with_gct')
                ->label('Amount w/ GCT')
                ->fillRecordUsing(function (Invoice $record, array $data) {
                    $record->amount_with_gct = static::castToFloat($data['amount_with_gct'] ?? 0);
                }),

            ImportColumn::make('currency')
                ->label('Currency')
                ->fillRecordUsing(function (Invoice $record, array $data) {
                    $record->currency = $data['currency'] ?? '';
                }),

            // Customer-related fields (not directly mapped to Invoice)
            ImportColumn::make('billed_to')
                ->label('Billed To')
                ->fillRecordUsing(function () {
                    return; // No mapping to Invoice
                }),

            ImportColumn::make('tax_id')
                ->label('Tax ID')
                ->fillRecordUsing(function () {
                    return; // No mapping to Invoice
                }),

            ImportColumn::make('billing_address')
                ->label('Billing Address')
                ->fillRecordUsing(function () {
                    return; // No mapping to Invoice
                }),

            ImportColumn::make('province')
                ->label('Province')
                ->fillRecordUsing(function () {
                    return; // No mapping to Invoice
                }),

            ImportColumn::make('client_email')
                ->label('Client Email')
                ->fillRecordUsing(function () {
                    return;
                }),

            ImportColumn::make('method_of_online_payment')
                ->label('Method of Online Payment')
                ->fillRecordUsing(function () {
                    return; // No mapping to Invoice
                }),

            ImportColumn::make('cardholder')
                ->label('Cardholder')
                ->fillRecordUsing(function () {
                    return; // No mapping to Invoice
                }),

            ImportColumn::make('card_type')
                ->label('Card Type')
                ->fillRecordUsing(function () {
                    return; // No mapping to Invoice
                }),

            ImportColumn::make('last_4_digits')
                ->label('Last 4 Digits')
                ->fillRecordUsing(function () {
                    return; // No mapping to Invoice
                }),

            ImportColumn::make('exp_date_of_card')
                ->label('Exp. Date of Card')
                ->fillRecordUsing(function () {
                    return; // No mapping to Invoice
                }),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('company_id')
                ->label('Company')
                ->options(fn() => Company::all()->pluck('name', 'id'))
                ->searchable()
                ->dehydrated(fn(Company $company): array => ['id' => $company->id, 'name' => $company->name])
                ->createOptionForm(CompanyResource::formSchema())
                ->createOptionUsing(fn(array $data): Company => Company::create($data))
                ->required(),
        ];
    }


    public static function headingRow(): ?int
    {
        return 4;
    }


    /**
     * Cast a numeric string to float with 3 decimal palces
     *
     * eg: 1,000.00 => 1000.00
     */
    protected static function castToFloat(mixed $val): float
    {
        return (float)filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public function resolveRecord(): ?Invoice
    {
        return Invoice::firstOrNew([
            // Update existing records, matching them by `$this->data['column_name']`
            'is_cancelled' => false,
            'order_id' => $this->data['tax_invoice'],
        ]);
    }

    public function saveRecord(): void
    {
        /** @var Invoice $invoice */
        $invoice = $this->record;

        /** @var ?Customer $customer */
        $customer = null;

        try {
            $customer = $this->createOrGetCustomer();

            $invoice->customer_id = $customer->id;

            $invoice->company_id = $this->options['company_id'];

            Log::info('Client created');

            $invoice->save();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            dd($exception);
            // The exception occurs due invalid fields in the customer or invoice data
            return;
        }

        try {
            $invoice->generateInvoice()->save();
            Log::info('Invoice generated');
        } catch (\Exception $exception) {
            Log::error('Failed to generate invoice for invoice ID ' . $invoice->id, ['exception' => $exception]);
        }

//        Mail::to($customer->email)->send(new InvoiceProcessed(invoice: $invoice));


        try {
            Mail::to($customer->email ?? 'gd.hon@beltran-urquiza.com')->send(new InvoiceProcessed(invoice: $invoice));
        } catch (\Exception $e) {
            Log::error('Failed to send invoice email for invoice ID ' . $invoice->id, ['exception' => $e]);
        }
        return;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your invoice import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    protected function createOrGetCustomer(): Customer
    {
        $data = $this->data;

        Log::info($data);

        // Check if the customer already exists based on email
        return Customer::updateOrCreate(
            ['email' => $data['client_email']],
            [
                'name' => $data['billed_to'],
                'tax_id' => $data['tax_id'],
                'billing_address' => $data['billing_address'],
                'province' => $data['province'],
                'payment_method' => $data['method_of_online_payment'],
                'cardholder' => $data['cardholder'],
                'card_type' => $data['card_type'],
                'card_last4' => $data['last_4_digits'],
                'card_exp_date' => $data['exp_date_of_card'],
            ]
        );
    }
}
