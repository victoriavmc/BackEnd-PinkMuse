<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante - PinkMuse</title>
    <style>
        @page { margin: 0px; }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        /* ENCABEZADO */
        header {
            background-color: #ff4b8b;
            color: #fff;
            padding: 30px 20px 60px 20px;
            text-align: left;
            position: relative;
        }

        header h1 {
            font-size: clamp(24px, 5vw, 36px);
            margin: 0;
            letter-spacing: 1px;
        }

        header .logo {
            position: absolute;
            right: 20px;
            top: 20px;
            text-align: center;
        }

        header .logo .logo-placeholder {
            width: 70px;
            height: 70px;
            background-color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #ff4b8b;
            font-size: 12px;
        }

        header .logo p {
            margin: 5px 0 0 0;
            font-family: 'Brush Script MT', cursive;
            font-size: 16px;
        }

        header .logo small {
            font-size: 12px;
        }

        /* DATOS DEL CLIENTE */
        .info {
            padding: 20px;
            background-color: #fff;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .info div {
            margin-bottom: 10px;
        }

        .info p {
            margin: 5px 0;
        }

        .info strong {
            color: #ff4b8b;
        }

        /* TABLA */
        .table-container {
            padding: 0 20px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
            min-width: 500px;
        }

        thead {
            background-color: #ff4b8b;
            color: white;
        }

        th, td {
            padding: 12px 10px;
            text-align: left;
        }

        th {
            font-weight: 600;
        }

        tbody tr:nth-child(even) {
            background-color: #fdf4f8;
        }

        tbody tr:hover {
            background-color: #fff0f6;
        }

        /* TOTALES */
        .totales {
            text-align: right;
            padding: 10px 20px;
            font-size: 14px;
        }

        .totales p {
            margin: 8px 0;
        }

        .totales strong {
            font-size: 18px;
            color: #ff4b8b;
        }

        /* PIE DE PÁGINA */
        footer {
            margin-top: 30px;
            background-color: #fff0f6;
            padding: 25px 20px;
            font-size: 13px;
        }

        footer .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
        }

        footer .pay-info {
            color: #444;
            flex: 1;
            min-width: 200px;
        }

        footer .pay-info p {
            margin: 5px 0;
        }

        footer .pay-info strong {
            color: #ff4b8b;
        }

        footer .thankyou {
            font-family: 'Brush Script MT', cursive;
            font-size: 32px;
            color: #ff007f;
            flex: 0 0 auto;
        }

        /* RESPONSIVE PARA TABLETS */
        @media (max-width: 768px) {
            header {
                padding: 25px 15px 50px 15px;
            }

            header .logo .logo-placeholder {
                width: 60px;
                height: 60px;
            }

            .info {
                padding: 15px;
                grid-template-columns: 1fr;
            }

            .table-container {
                padding: 0 15px;
            }

            table {
                font-size: 13px;
                min-width: 450px;
            }

            th, td {
                padding: 10px 8px;
            }

            .totales {
                padding: 10px 15px;
            }

            footer {
                padding: 20px 15px;
            }

            footer .thankyou {
                font-size: 28px;
            }
        }

        /* RESPONSIVE PARA MÓVILES */
        @media (max-width: 600px) {
            header {
                text-align: center;
                padding: 20px 15px 30px 15px;
            }

            header .logo {
                position: static;
                margin-top: 15px;
            }

            header .logo .logo-placeholder {
                margin: 0 auto;
            }

            .info {
                padding: 15px;
            }

            .table-container {
                padding: 0 10px;
            }

            table {
                font-size: 12px;
                min-width: 100%;
            }

            th, td {
                padding: 8px 5px;
            }

            th:nth-child(2), td:nth-child(2) {
                text-align: center;
            }

            th:nth-child(3), td:nth-child(3),
            th:nth-child(4), td:nth-child(4) {
                text-align: right;
            }

            .totales {
                text-align: right;
                padding: 10px;
                font-size: 13px;
            }

            .totales strong {
                font-size: 16px;
            }

            footer {
                padding: 20px 15px;
            }

            footer .footer-content {
                flex-direction: column;
            }

            footer .thankyou {
                font-size: 24px;
                text-align: center;
                width: 100%;
                margin-top: 15px;
            }
        }

        /* RESPONSIVE PARA MÓVILES MUY PEQUEÑOS */
        @media (max-width: 400px) {
            header h1 {
                font-size: 22px;
            }

            table {
                font-size: 11px;
            }

            th, td {
                padding: 6px 3px;
            }

            .totales {
                font-size: 12px;
            }

            footer {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>PinkMuse</h1>
    <div class="logo">
        <div class="logo-placeholder">
            @if(!empty($empresa['logo']))
                <img src="{{ $empresa['logo'] }}" alt="Logo" width="70" height="70" style="border-radius:50%;">
            @else
                LOGO
            @endif
        </div>
        <p>Aldo & VictoriaVMC</p>
    </div>
</header>

<section class="info">
    <div>
        <p><strong>Comprobante N°:</strong> {{ $Comprobante['numero'] ?? '---' }}</p>
        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($Comprobante['fecha'] ?? now())->format('d/m/Y') }}</p>
    </div>
    <div>
        <p><strong>Cliente:</strong> {{ $cliente['nombre'] ?? 'Cliente' }}</p>
        @if(!empty($cliente['email']))
            <p><strong>Email:</strong> {{ $cliente['email'] }}</p>
        @endif
    </div>
</section>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Item Description</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $item)
                <tr>
                    <td>{{ $item['descripcion'] ?? '---' }}</td>
                    <td>{{ $item['cantidad'] ?? 0 }}</td>
                    <td>${{ number_format($item['precio'], 2, ',', '.') }}</td>
                    <td>${{ number_format(($item['cantidad'] ?? 0) * ($item['precio'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;">No hay productos</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@php
    $subtotal = collect($productos)->sum(fn($p) => ($p['cantidad'] ?? 0) * ($p['precio'] ?? 0));
    $impuesto = $factura['impuesto'] ?? 0.10;
    $tax = $subtotal * $impuesto;
    $total = $subtotal + $tax;
@endphp

<div class="totales">
    <p>Subtotal: ${{ number_format($subtotal, 2, ',', '.') }}</p>
    <p>Tax ({{ $impuesto * 100 }}%): ${{ number_format($tax, 2, ',', '.') }}</p>
    <p><strong>Grand Total: ${{ number_format($total, 2, ',', '.') }}</strong></p>
</div>

<footer>
    <div class="footer-content">
        <div class="pay-info">
            <p><strong>Bank Name:</strong> {{ $empresa['banco'] ?? 'Thynk Unlimited' }}</p>
            <p><strong>Bank No:</strong> {{ $empresa['cuenta'] ?? '123-456-7890' }}</p>
            <p><strong>Email:</strong> {{ $empresa['email'] ?? 'hello@reallygreatsite.com' }}</p>
        </div>
        <div class="thankyou">{{ $mensaje ?? 'Gracias por el apoyo!' }}</div>
    </div>
</footer>

</body>
</html>
