<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>FACTURA #{{ $invoice->id }}</title>
    <meta name="author" content="Jose Alejandro Melendez">
</head>
<body style="display: flex; justify-content: center; font-family: Arial, sans-serif;">

<div style="width: 100%; background-color: #fff; padding: 20px;">
    <p style="margin-top: 10px; font-size: 10pt; font-weight: bold;">{{ $invoice->company->name }}</p>

    <table style="width: 100%; margin-top: 10px; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <p style="color: black; font-size: 8pt;">R.T.N: {{ $invoice->company->tax_id }}</p>
                <p style="color: black; font-size: 8pt;">[Dirección]: {{ $invoice->company->address }}</p>
                <p style="color: black; font-size: 8pt;">[Ciudad]: {{ $invoice->company->city }} {{ $invoice->company->state }}</p>
                <p style="color: black; font-size: 8pt;">[Número de teléfono]: {{ $invoice->company->phone }}</p>
                <p style="color: black; font-size: 8pt;">Correo electrónico: {{ $invoice->company->email }}</p>
                <p style="color: black; font-size: 8pt;">CAI:</p>
                <p style="color: black; font-size: 8pt;">Rango autorizado:</p>
                <p style="color: black; font-size: 8pt;">Fecha límite de emisión:</p>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <p style="color: black; font-weight: bold; font-size: 8pt;">FECHA DE FACTURA:</p>
                <p style="color: black; font-size: 8pt;">{{ \Carbon\Carbon::parse($invoice->transaction_date)->format('d/m/Y') }}</p>
                <p style="color: black; font-weight: bold; font-size: 8pt;">Nº DE FACTURA</p>
                <p style="color: black; font-size: 8pt;">{{ $invoice->id }}</p>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 10px; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <p style="color: black; font-weight: bold; font-size: 8pt;">FACTURAR A:</p>
                <p style="color: black; font-size: 8pt;">Cliente: {{ $invoice->customer->name }}</p>
                <p style="color: black; font-size: 8pt;">R.T.N.: {{ $invoice->customer->tax_id }}</p>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <p style="color: black; font-weight: bold; font-size: 8pt;">ENVIAR A:</p>
                <p style="color: black; font-size: 8pt;">{{ $invoice->customer->name }}</p>
                <p style="color: black; font-size: 8pt;">{{ $invoice->customer->billing_address }}</p>
                <p style="color: black; font-size: 8pt;">{{ $invoice->customer->email }}</p>
                <p style="color: black; font-size: 8pt;">{{ $invoice->customer->province }}</p>
                <p style="color: black; font-size: 8pt;">{{ $invoice->customer->phone }}</p>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 10px; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #F1F1F1; border: 1px solid #000;">
                <td style="width: 40px; text-align: center; border: 1px solid #000; color: black; font-weight: bold; font-size: 8pt;">CANTIDAD</td>
                <td style="width: 60px; text-align: center; border: 1px solid #000; color: black; font-weight: bold; font-size: 8pt;">DESCRIPCIÓN</td>
                <td style="width: 100px; text-align: center; border: 1px solid #000; color: black; font-weight: bold; font-size: 8pt;">PRECIO UNITARIO</td>
                <td style="width: 150px; text-align: center; border: 1px solid #000; color: black; font-weight: bold; font-size: 8pt;">DESCUENTOS Y REBAJAS</td>
                <td style="width: 120px; text-align: center; border: 1px solid #000; color: black; font-weight: bold; font-size: 8pt;">TOTAL</td>
            </tr>
        </thead>
        <tbody>
            <tr style="height: 150px;">
                <td style="height: 150px; text-align: center; border: 1px solid #000; color: black; font-size: 8pt;">{{ $invoice->quantity }}</td>
                <td style="border: 1px solid #000; color: black; font-size: 8pt;">{{ $invoice->service_purchased }}</td>
                <td style="text-align: right; border: 1px solid #000; color: black; font-size: 8pt;">{{ number_format($invoice->amount_without_gct / $invoice->quantity, 2) }}</td>
                <td style="text-align: right; border: 1px solid #000; color: black; font-size: 8pt;">-</td>
                <td style="background-color: #F1F1F1; text-align: right; border: 1px solid #000; color: black; font-weight: bold; font-size: 8pt;">{{ number_format($invoice->amount_without_gct, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3"></td>
                <td style="text-align: right; border: 1px solid #000; color: black; font-weight: bold; font-size: 8pt;">Total L.</td>
                <td style="background-color: #F1F1F1; text-align: right; border: 1px solid #000; color: black; font-weight: bold; font-size: 8pt;">{{ number_format($invoice->amount_without_gct, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
        <tr>
            <td colspan="3"></td>
            <td style="text-align: right; color: black; font-size: 8pt;">Importe exonerado L.</td>
            <td style="background-color: #F1F1F1; border: 1.5px solid #000; text-align: right; color: black; font-size: 8pt;">-</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td style="text-align: right; color: black; font-size: 8pt;">Importe exento L.</td>
            <td style="background-color: #F1F1F1; border: 1.5px solid #000; text-align: right; color: black; font-size: 8pt;">-</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td style="text-align: right; color: black; font-size: 8pt;">Importe gravado 15% L.</td>
            <td style="background-color: #F1F1F1; border: 1.5px solid #000; text-align: right; color: black; font-size: 8pt;">{{ number_format($invoice->amount_without_gct, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td style="text-align: right; color: black; font-size: 8pt;">Importe gravado 18% L.</td>
            <td style="background-color: #F1F1F1; border: 1.5px solid #000; text-align: right; color: black; font-size: 8pt;">-</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td style="text-align: right; color: black; font-size: 8pt;">ISV 15% L.</td>
            <td style="background-color: #F1F1F1; border: 1.5px solid #000; text-align: right; color: black; font-size: 8pt;">{{ number_format($invoice->gct, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td style="text-align: right; color: black; font-size: 8pt;">ISV 18% L.</td>
            <td style="background-color: #F1F1F1; border: 1.5px solid #000; text-align: right; color: black; font-size: 8pt;">-</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td style="text-align: right; font-weight: bold; color: black; font-size: 8pt;">Total a pagar L.</td>
            <td style="background-color: #F1F1F1; border: 2px solid #000; text-align: right; color: black; font-weight: bold; font-size: 8pt;">{{ number_format($invoice->amount_with_gct, 2) }}</td>
        </tr>
    </table>    

    <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
        <tr>
            <td><p style="color: black; font-weight: bold; font-size: 6pt;">Total a pagar en letras:</p></td>
        </tr>
        <tr>
            <td>
                <p style="color: black; font-weight: bold; font-size: 8pt;">
                    {{ ucfirst(\Illuminate\Support\Number::spell((int) $invoice->amount_with_gct, 'es') . ' Lempiras con ' . number_format(($invoice->amount_with_gct - (int) $invoice->amount_with_gct) * 100, 0) . '/100') }}
                </p>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
        <tr>
            <td style="font-weight: bold; color: black; font-size: 8pt;">Datos del adquiriente Exonerado</td>
        </tr>
        <tr>
            <td style="width: 50%; border: 1px solid #000; color: black; font-size: 8pt;">Nro. Correlativo de la orden de compra exenta:</td>
            <td style="width: 50%; border: 1px solid #000;"></td>
        </tr>
        <tr>
            <td style="width: 50%; border: 1px solid #000; color: black; font-size: 8pt;">Nro. Correlativo de la constancia del registro de exonerados:</td>
            <td style="width: 50%; border: 1px solid #000;"></td>
        </tr>
        <tr>
            <td style="width: 50%; border: 1px solid #000; color: black; font-size: 8pt;">Nro. Identificativo del registro SAG</td>
            <td style="width: 50%; border: 1px solid #000;"></td>
        </tr>
    </table>

    <table style="width: 25%; margin-top: 20px; border-collapse: collapse;">
        <tr>
            <td style="color: black; font-size: 8pt;">ORIGINAL:</td>
            <td style="color: black; font-size: 8pt;">CLIENTE</td>
        </tr>
        <tr>
            <td style="color: black; font-size: 8pt;">COPIA:</td>
            <td style="color: black; font-size: 8pt;">EMISOR</td>
        </tr>
    </table>
</div>

</body>
</html>
