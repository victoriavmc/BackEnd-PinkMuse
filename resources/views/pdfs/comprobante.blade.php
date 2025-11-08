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
        <div class="logo-placeholder">LOGO</div>
        <p>Aldo & VictoriaVMC<br><small>Fashion Store</small></p>
    </div>
</header>

<section class="info">
    <div>
        <p><strong>Factura N°:</strong> 20212</p>
        <p><strong>Fecha:</strong> 08/11/2025</p>
    </div>
    <div>
        <p><strong>Cliente:</strong> Lorna Alvarado</p>
        <p><strong>Email:</strong> lorna@example.com</p>
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
            <tr>
                <td>Dress</td>
                <td>1</td>
                <td>$59.00</td>
                <td>$59.00</td>
            </tr>
            <tr>
                <td>Hoodie</td>
                <td>1</td>
                <td>$69.00</td>
                <td>$69.00</td>
            </tr>
            <tr>
                <td>Heels</td>
                <td>2</td>
                <td>$39.00</td>
                <td>$78.00</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="totales">
    <p>Subtotal: $206.00</p>
    <p>Tax (10%): $20.60</p>
    <p><strong>Grand Total: $226.60</strong></p>
</div>

<footer>
    <div class="footer-content">
        <div class="pay-info">
            <p><strong>Bank Name:</strong> Thynk Unlimited</p>
            <p><strong>Bank No:</strong> 123-456-7890</p>
            <p><strong>Email:</strong> hello@reallygreatsite.com</p>
        </div>
        <div class="thankyou">Thank You</div>
    </div>
</footer>

</body>
</html>
