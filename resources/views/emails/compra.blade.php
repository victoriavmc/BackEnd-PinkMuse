<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pink Muse - Compra Confirmada</title>
    <style>
        @media only screen and (max-width: 600px) {
            .container {
                width: 90% !important;
                padding: 20px !important;
            }
            h1 {
                font-size: 24px !important;
            }
            p, li {
                font-size: 15px !important;
            }
            .btn {
                padding: 12px 22px !important;
                font-size: 15px !important;
            }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#fdf3f7; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;">
    <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#fdf3f7; padding:20px 0;">
        <tr>
            <td align="center">
                <table class="container" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color:#ffffff; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08); overflow:hidden;">
                    
                    <!-- Encabezado -->
                    <tr>
                        <td align="center" style="padding:40px 20px 10px;">
                            <h1 style="color:#e75480; font-size:28px; margin:0; font-weight:700;">🌸 Pink Muse</h1>
                            <p style="color:#777; font-size:15px; margin-top:5px;">Tu estilo, tu arte, tu momento</p>
                        </td>
                    </tr>

                    <!-- Cuerpo -->
                    <tr>
                        <td style="padding:30px 40px 20px; color:#444;">
                            <h2 style="color:#e75480; font-size:22px; margin-bottom:15px;">¡Compra Confirmada!</h2>
                            <p style="font-size:16px; margin-bottom:20px;">¡Gracias por tu compra! 🎉</p>

                            <p style="font-size:15px; line-height:1.6; margin-bottom:15px;">
                                A continuación, los detalles de tu orden:
                            </p>

                            <ul style="font-size:15px; color:#555; line-height:1.7; margin-bottom:25px;">
                                <li>{{ $tipo_compra }}: <strong>{{ $nombre }}</strong></li>
                                <li>Fecha y hora: <strong>{{ $fecha_hora}}</strong></li>
                                <li>Total pagado: <strong>{{ $monto_total }}</strong></li>
                            </ul>

                            <p style="font-size:15px; margin-bottom:25px;">
                                Podés presentar esta orden de compra o descargar tus entradas en nuestro sitio web.
                            </p>

                            <div style="text-align:center; margin-bottom:30px;">
                                <a href="{{ $orderUrl }}" 
                                   class="btn"
                                   style="background-color:#e75480; color:#fff; text-decoration:none; padding:14px 30px; border-radius:25px; font-size:16px; display:inline-block;">
                                   Ver mi compra
                                </a>
                            </div>

                            <p style="font-size:13px; color:#999; text-align:center; word-break:break-all;">
                                O copiá y pegá este enlace en tu navegador:<br>
                                <a href="{{ $orderUrl }}" style="color:#e75480;">{{ $orderUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Pie -->
                    <tr>
                        <td style="text-align:center; background-color:#fdf3f7; padding:20px; border-top:1px solid #f2d8e2;">
                            <p style="font-size:12px; color:#b48ea9; margin:0;">
                                © {{ date('Y') }} Pink Muse. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
