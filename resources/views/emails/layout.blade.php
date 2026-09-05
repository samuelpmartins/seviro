<!doctype html>
<html lang="pt-BR" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
    <title>Sevirô</title>
    <!--[if mso]>
  <noscript>
    <xml>
      <o:OfficeDocumentSettings>
        <o:PixelsPerInch>96</o:PixelsPerInch>
      </o:OfficeDocumentSettings>
    </xml>
  </noscript>
  <![endif]-->
    <style>
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        table {
            border-collapse: collapse !important;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            outline: none;
            text-decoration: none;
        }

        a {
            color: #3498db;
        }

        @media only screen and (max-width: 640px) {
            .email-shell {
                width: 100% !important;
            }

            .mobile-padding {
                padding-left: 22px !important;
                padding-right: 22px !important;
            }

            .header-padding {
                padding: 26px 22px !important;
            }

            .footer-padding {
                padding: 24px 22px !important;
            }

            .brand-logo {
                width: 190px !important;
                max-width: 190px !important;
            }
        }
    </style>
</head>

<body
    style="margin:0; padding:0; width:100%; background-color:#f1f4f6; font-family:Arial, Helvetica, sans-serif; color:#2c3e50;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent; mso-hide:all;">
        {{ $preheader ?? 'Mensagem automática da Sevirô.' }}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="width:100%; background-color:#f1f4f6;">
        <tr>
            <td align="center" style="padding:32px 12px;">
                <!--[if mso]>
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"><tr><td>
        <![endif]-->
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                    class="email-shell"
                    style="width:100%; max-width:600px; background-color:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 8px 28px rgba(44,62,80,0.10);">
                    <tr>
                        <td class="header-padding" align="center" bgcolor="#2c3e50"
                            style="padding:30px 36px; background-color:#2c3e50; border-bottom:4px solid #3498db;">
                            <a href="https://seviro.com.br/" target="_blank"
                                style="display:inline-block; text-decoration:none;">
                                <img src="https://seviro.com.br/storage/img/logo.png" width="220" class="brand-logo"
                                    alt="Sevirô"
                                    style="display:block; width:220px; max-width:220px; height:auto; color:#ffffff; font-size:24px; font-weight:bold; line-height:32px;">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="mobile-padding"
                            style="padding:40px 44px 36px; background-color:#ffffff; color:#2c3e50; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:1.65;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td class="footer-padding" align="center" bgcolor="#f8f9fa"
                            style="padding:26px 36px; background-color:#f8f9fa; border-top:1px solid #e9ecef; color:#6c757d; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:1.6;">
                            <p style="margin:0 0 8px; color:#2c3e50; font-size:13px; font-weight:bold;">Sevirô — Gestão
                                simples para o seu restaurante</p>
                            <p style="margin:0 0 10px;">Este é um e-mail automático. Se precisar de ajuda, fale com a
                                nossa equipe.</p>
                            <p style="margin:0;">
                                <a href="https://seviro.com.br/" target="_blank"
                                    style="color:#3498db; font-weight:bold; text-decoration:none;">seviro.com.br</a>
                                &nbsp;&bull;&nbsp;
                                <a href="mailto:oficial@seviro.com.br"
                                    style="color:#3498db; text-decoration:none;">oficial@seviro.com.br</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <!--[if mso]>
        </td></tr></table>
        <![endif]-->
            </td>
        </tr>
    </table>
</body>

</html>
