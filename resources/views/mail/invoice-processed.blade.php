<x-mail::message>
<div style="max-width: 800px; margin: 0 auto;">
<!-- Header -->
<div style="text-align: center; margin-bottom: 50px;">
<h1 style="margin: 0;">INVOICE</h1>
<p style="margin: 0;">{{ $invoice->company?->name ?? 'Company Name' }}</p>
<p style="margin: 0;">{{ $invoice->company?->address ?? 'Company Address' }}</p>
<p style="margin: 0;">Phone: {{ $invoice->company?->phone ?? 'Company Phone' }}</p>
</div>

<!-- Invoice Info -->
<div style="margin-bottom: 30px;">
<div style="width: 50%; float: left;">
<p><strong>Billed To:</strong></p>
<p>{{ $invoice->customer->name }}</p>
<p>{{ $invoice->customer->billing_address }}</p>
<p>{{ $invoice->customer->province }}</p>
<p>Email: {{ $invoice->customer->email }}</p>
<p>Tax ID: {{ $invoice->customer->tax_id }}</p>
</div>
<div style="width: 50%; float: right; text-align: right;">
<p><strong>Invoice #:</strong> {{ $invoice->id }}</p>
<p><strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->transaction_date)->format('F d, Y') }}</p>
<p><strong>Transaction Ref:</strong> {{ $invoice->transaction_ref }}</p>
<p><strong>Order ID:</strong> {{ $invoice->order_id }}</p>
</div>
<div style="clear: both;"></div>
</div>

<!-- Table -->
<table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
<thead>
<tr>
<th style="border: 1px solid #dddddd; text-align: left; padding: 8px;">Description</th>
<th style="border: 1px solid #dddddd; text-align: right; padding: 8px;">Quantity</th>
<th style="border: 1px solid #dddddd; text-align: right; padding: 8px;">Unit Price</th>
<th style="border: 1px solid #dddddd; text-align: right; padding: 8px;">Amount</th>
</tr>
</thead>
<tbody>
<tr>
<td style="border: 1px solid #dddddd; padding: 8px;">{{ $invoice->service_purchased }}</td>
<td style="border: 1px solid #dddddd; text-align: right; padding: 8px;">{{ $invoice->quantity }}</td>
<td style="border: 1px solid #dddddd; text-align: right; padding: 8px;">
    {{ $invoice->currency }}{{ number_format($invoice->amount_without_gct / $invoice->quantity, 2) }}
</td>
<td style="border: 1px solid #dddddd; text-align: right; padding: 8px;">
    {{ $invoice->currency }}{{ number_format($invoice->amount_without_gct, 2) }}
</td>
</tr>
</tbody>
</table>

<!-- Totals -->
<div style="text-align: right;">
<p><strong>Subtotal:</strong> {{ $invoice->currency }}{{ number_format($invoice->amount_without_gct, 2) }}</p>
<p><strong>GCT ({{ number_format(($invoice->gct / $invoice->amount_without_gct) * 100, 2) }}%):</strong> {{ $invoice->currency }}{{ number_format($invoice->gct, 2) }}</p>
<p><strong>Total:</strong> {{ $invoice->currency }}{{ number_format($invoice->amount_with_gct, 2) }}</p>
</div>

<!-- Footer -->
<div style="text-align: center; margin-top: 50px;">
<p>Thank you for your business!</p>
</div>
</div>
</x-mail::message>
