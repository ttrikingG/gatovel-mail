<?php

declare(strict_types=1);

namespace Gatovel\Mail\themes;

final class GatovelTheme
{
    public function render(string $content): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gatovel Mail</title>
</head>
<body style="margin:0;padding:0;background-color:#0f172a;color:#e2e8f0;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#0f172a;">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:600px;background-color:#1e293b;border:1px solid #334155;">
                    <tr>
                        <td style="padding:24px 32px;border-bottom:3px solid #22c55e;">
                            <h1 style="margin:0;color:#22c55e;font-size:24px;">
                                Gatovel Framework
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;color:#e2e8f0;font-size:16px;line-height:1.6;">
                            {$content}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;border-top:1px solid #334155;color:#94a3b8;font-size:12px;text-align:center;">
                            Enviado com Gatovel Mail
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
