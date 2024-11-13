<x-mail::message>
<style type="text/css">
* {
margin: 0;
padding: 0;
text-indent: 0;
}
body {
display: flex;
justify-content: center;
/*padding: 70px;*/
font-family: Arial, sans-serif;
background-color: #f9f9f9;
}
.invoice-container {
width: 100%;
max-width: 800px;
background-color: #fff;
padding: 20px;
box-sizing: border-box;
border: 1px solid #ccc;
}
.s1 {
color: black;
font-family: Arial, sans-serif;
font-size: 8pt;
font-weight: normal;
}
.s2 {
color: black;
font-family: Arial, sans-serif;
font-size: 8pt;
font-weight: bold;
}
p {
color: black;
font-family: Arial, sans-serif;
font-size: 8.5pt;
font-weight: bold;
margin: 0pt;
}
.s3 {
color: black;
font-family: Arial, sans-serif;
font-size: 6pt;
font-weight: bold;
}
.flex-container {
display: flex;
justify-content: space-between;
gap: 20px;
margin-top: 10px;
}
table {
width: 100%;
margin-top: 10px;
border-collapse: collapse;
}
td {
padding: 5px;
}
</style>
<div class="invoice-container">
<p style="margin-top: 10px; font-size: 10pt; font-weight: bold;">{{ $invoice->company->name }}</p>

<div style="align-items: flex-start;" class="flex-container">
<table>
<tr>
<td><p class="s1">R.T.N: {{ $invoice->company->tax_id }}</p></td>
</tr>
<tr>
<td><p class="s1">[Dirección]: {{ $invoice->company->address }}</p></td>
</tr>
<tr>
<td><p class="s1">[Ciudad]: {{ $invoice->company->city }} {{ $invoice->company->state }}</p></td>
</tr>
<tr>
<td><p class="s1">[Número de teléfono]: {{ $invoice->company->phone }}</p></td>
</tr>
<tr>
<td><p class="s1">Correo electrónico: {{ $invoice->company->email }}</p></td>
</tr>
<tr>
<td><p class="s1">CAI:</p></td>
</tr>
<tr>
<td><p class="s1">Rango autorizado:</p></td>
</tr>
<tr>
<td><p class="s1">Fecha límite de emisión:</p></td>
</tr>
</table>

<table>
<tr>
<td><p class="s2">FECHA DE FACTURA:</p></td>
{{--<td><p class="s1"> {{ \Carbon\Carbon::parse($invoice->transaction_date)->format('d/m/Y') }}</p></td>--}}
</tr>
<tr>
<td><p class="s1">{{ \Carbon\Carbon::parse($invoice->transaction_date)->format('d/m/Y') }}</p></td>
</tr>
<tr>
<td></td>
</tr>
<tr>
<td><p class="s2">Nº DE FACTURA</p></td>
{{--<td><p class="s1">{{ $invoice->id }}</p></td>--}}
</tr>
<tr>
<td><p class="s1">{{ $invoice->id }}</p></td>
</tr>
</table>
</div>

<div style="align-items: flex-start;" class="flex-container" >
<table>
<tr>
<td><p class="s2">FACTURAR A:</p></td>
</tr>
<tr>
<td><p class="s1">Cliente: {{ $invoice->customer->name }}</p></td>
</tr>
<tr>
<td><p class="s1">R.T.N.: {{ $invoice->customer->tax_id }}</p></td>
</tr>
</table>

<table>
<tr>
<td><p class="s2">ENVIAR A:</p></td>
</tr>
<tr>
<td><p class="s1">{{ $invoice->customer->name }}</p></td>
</tr>
<tr>
<td><p class="s1">{{ $invoice->customer->billing_address }}</p></td>
</tr>
<tr>
<td><p class="s1">{{ $invoice->customer->email }}</p></td>
</tr>
<tr>
<td><p class="s1">{{ $invoice->customer->province }}</p></td>
</tr>
<tr>
<td><p class="s1">{{ $invoice->customer->phone }}</p></td>
</tr>
</table>
</div>

<table style="">
<thead>
<tr style="background-color: #F1F1F1; border: 1px solid #000;">
<td style="width: 40px; border: 1px solid #000;"><p class="s2" style="text-align: center;">CANTIDAD</p></td>
<td style="width: 60px; border: 1px solid #000;"><p class="s2" style="text-align: center;">DESCRIPCIÓN</p></td>
<td style="width: 100px; border: 1px solid #000;"><p class="s2" style="text-align: center;">PRECIO UNITARIO</p></td>
<td style="width: 150px; border: 1px solid #000;"><p class="s2" style="text-align: center;">DESCUENTOS Y REBAJAS</p></td>
<td style="width: 120px; border: 1px solid #000;"><p class="s2" style="text-align: center;">TOTAL</p></td>
</tr>
</thead>
<tbody>
<tr style="height: 250px;">
<td style="text-align: center; border: 1px solid #000;"><p class="s1">{{ $invoice->quantity }}</p></td>
<td style="border: 1px solid #000;"><p class="s1">{{ $invoice->service_purchased }}</p></td>
<td style="text-align: right; border: 1px solid #000;"><p class="s1">{{ number_format($invoice->amount_without_gct / $invoice->quantity, 2) }}</p></td>
<td style="text-align: right; border: 1px solid #000;"><p class="s1">-</p></td>
<td style="background-color: #F1F1F1; text-align: right; border: 1px solid #000;"><p class="s2"> {{ number_format($invoice->amount_without_gct, 2) }}</p></td>
</tr>
<tr>
<td colspan="3"></td>
<td style="text-align: right; border: 1px solid #000;"><p class="s2">Total L.</p></td>
<td style="background-color: #F1F1F1; text-align: right; border: 1px solid #000;"><p class="s2"> {{ number_format($invoice->amount_without_gct, 2) }}</p></td>
</tr>
</tbody>
</table>

<table style="margin-top: 20px;">
<thead>
<tr style="">
<td style="width: 40px;"></td>
<td style="width: 60px;"></td>
<td style="width: 100px;"></td>
<td style="width: 150px;"><p class="s2" style="text-align: right;"></p></td>
<td style="width: 120px;"><p class="s2" style="text-align: right;"></p></td>
</tr>
</thead>
<tbody>
<tr>
<td colspan="3"></td>
<td><p class="s1" style="text-align: right;">Importe exonerado L.</p></td>
<td style="background-color: #F1F1F1; border-right: 1.5px solid #000; border-left: 1.5px solid #000; border-top: 1.5px solid #000;"><p class="s2" style="text-align: right;">-</p></td>
</tr>
<tr>
<td colspan="3"></td>
<td><p class="s1" style="text-align: right;">Importe exento L.</p></td>
<td style="background-color: #F1F1F1; border-right: 1.5px solid #000; border-left: 1.5px solid #000;"><p class="s2" style="text-align: right;">-</p></td>
</tr>
<tr>
<td colspan="3"></td>
<td><p class="s1" style="text-align: right;">Importe gravado 15% L.</p></td>
<td style="background-color: #F1F1F1; border-right: 1.5px solid #000; border-left: 1.5px solid #000;"><p class="s2" style="text-align: right;"> {{ number_format($invoice->amount_without_gct, 2) }}</p></td>
</tr>
<tr>
<td colspan="3"></td>
<td><p class="s1" style="text-align: right;">Importe gravado 18% L.</p></td>
<td style="background-color: #F1F1F1; border-right: 1.5px solid #000; border-left: 1.5px solid #000;"><p class="s2" style="text-align: right;">-</p></td>
</tr>
<tr>
<td colspan="3"></td>
<td><p class="s1" style="text-align: right;">ISV 15% L.</p></td>
<td style="background-color: #F1F1F1; border-right: 1.5px solid #000; border-left: 1.5px solid #000;"><p class="s2" style="text-align: right;">{{ number_format($invoice->gct, 2) }}</p></td>
</tr>
<tr>
<td colspan="3"></td>
<td><p class="s1" style="text-align: right;">ISV 18% L.</p></td>
<td style="background-color: #F1F1F1;  border-right: 1.5px solid #000; border-left: 1.5px solid #000;"><p class="s2" style="text-align: right;">-</p></td>
</tr>
<tr>
<td colspan="3"></td>
<td><p class="s2" style="text-align: right;">Total a pagar L.</p></td>
<td style="background-color: #F1F1F1;  border: 2px solid #000;"><p class="s2" style="text-align: right;">{{ number_format($invoice->amount_with_gct, 2) }}</p></td>
</tr>
</tbody>
</table>

<table style="margin-top: 20px;">
<tr>
<td><p class="s3">Total a pagar en letras:</p></td>
</tr>
<tr>
<td>
<p class="s2">
{{ ucfirst(\Illuminate\Support\Number::spell((int) $invoice->amount_with_gct, 'es') . ' Lempiras con ' . number_format(($invoice->amount_with_gct - (int) $invoice->amount_with_gct) * 100, 0) . '/100') }}
</p>
</td>
</tr>
</table>

<table style="margin-top: 20px; ">
<tr style="">
<td style=""><p class="s2">Datos del adquiriente Exonerado</p></td>
</tr>
<tr>
<td style="border: 1px solid #000; width: 50%;"><p class="s1">Nro. Correlativo de la orden de compra exenta:</p></td>
<td style="border: 1px solid #000; width: 50%;"></td>
</tr>
<tr>
<td style="border: 1px solid #000; width: 50%;"><p class="s1">Nro. Correlativo de la constancia del registro de exonerados:</p></td>
<td style="border: 1px solid #000; width: 50%;"></td>
</tr>
<tr>
<td style="border: 1px solid #000; width: 50%;"><p class="s1">Nro. Identificativo del registro SAG</p></td>
<td style="border: 1px solid #000; width: 50%;"></td>
</tr>
</table>

<table style="margin-top: 20px; width:25%;">
<tr>
<td><p class="s1">ORIGINAL:</p></td>
<td class="s1">CLIENTE</td>
</tr>
<tr>
<td><p class="s1">COPIA:</p></td>
<td class="s1">EMISOR</td>
</tr>
</table>
</div>
</x-mail::message>
