<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pink Muse - Restablecer contraseña</title>
</head>
<body style="margin:0; padding:0; background-color:#fdf3f7; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
    <table align="center" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px; margin:auto; background-color:#ffffff; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
        <tr>
            <td style="text-align:center; padding:40px 20px 10px;">
                <h1 style="color:#e75480; font-size:28px; margin:0; font-weight:700;">🌸 Pink Muse</h1>
                <p style="color:#777; font-size:15px; margin-top:5px;">Un toque de arte en cada detalle</p>
            </td>
        </tr>

        <tr>
            <td style="padding:30px 40px 20px; color:#444;">
                <p style="font-size:16px; margin-bottom:20px;">Hola,</p>
                <p style="font-size:15px; line-height:1.6; margin-bottom:25px;">
                    Hacé clic en el siguiente botón para restablecer tu contraseña. Si no solicitaste este cambio, simplemente ignorá este correo.
                </p>
                <div style="text-align:center; margin-bottom:30px;">
                    <a href="{{ $resetUrl }}" 
                       style="background-color:#e75480; color:#fff; text-decoration:none; padding:12px 28px; border-radius:25px; font-size:16px; display:inline-block;">
                       Restablecer contraseña
                    </a>
                </div>
                <p style="font-size:13px; color:#999; text-align:center;">
                    O copiá y pegá este enlace en tu navegador:<br>
                    <a href="{{ $resetUrl }}" style="color:#e75480;">{{ $resetUrl }}</a>
                </p>
            </td>
        </tr>

        <tr>
            <td style="text-align:center; background-color:#fdf3f7; padding:20px; border-top:1px solid #f2d8e2;">
                <p style="font-size:12px; color:#b48ea9; margin:0;">
                    © {{ date('Y') }} Pink Muse. Todos los derechos reservados.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
