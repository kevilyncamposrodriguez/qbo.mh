 <?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    require 'libs/PHPMailer/Exception.php';
    require 'libs/PHPMailer/PHPMailer.php';
    require 'libs/PHPMailer/SMTP.php';
    
    $clave = "50620032000310174741600100001010000001617187654321";
    $to = "k.campos@contafast.net";
    $client = "Contafast S.A";
    $idcard = "3101747416";
    
    $mail = new PHPMailer;
    $mail->isSMTP(); 
    $mail->SMTPKeepAlive = true;
    $mail->SMTPDebug = 2; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
    $mail->Host = "mail.smtp2go.com";//"smtp.gmail.com"; // use $mail->Host = gethostbyname('smtp.gmail.com'); // if your network does not support SMTP over IPv6
   
    $mail->Port = "587"; //587; // TLS only
    $mail->SMTPSecure = 'tls'; // ssl is depracated
    $mail->SMTPAuth = true;
    $mail->Username = "sincronizador";
    $mail->Password = "Contafast.2020";
    $mail->setFrom("sincronizador_qbo-mh@contafast.net", "Sincronizador QBO-MH");
    $mail->addAddress("k.camposr05@gmail.com");
    $mail->ClearAllRecipients( ); // clear all
    $mail->addAddress($to);
    $mail->addBCC('sincronizador_qbo-mh@contafast.net');
    $mail->Subject = 'Factura de '.$cliente;
    $mail->msgHTML('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link href="default.css" rel="stylesheet" />
    </head>
    <body>
        <style>
            /* Base */

            body,
            body *:not(html):not(style):not(br):not(tr):not(code) {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif,
                    "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
                box-sizing: border-box;
            }

            body {
                background-color: #000000;
                color: #74787e;
                height: 100%;
                hyphens: auto;
                line-height: 1.4;
                margin: 0;
                -moz-hyphens: auto;
                -ms-word-break: break-all;
                width: 100% !important;
                -webkit-hyphens: auto;
                -webkit-text-size-adjust: none;
                word-break: break-all;
                word-break: break-word;
            }

            p,
            ul,
            ol,
            blockquote {
                line-height: 1.4;
                text-align: left;
            }

            a {
                color: #3869d4;
            }

            a img {
                border: none;
            }

            /* Typography */

            h1 {
                color: #3d4852;
                font-size: 19px;
                font-weight: bold;
                margin-top: 0;
                text-align: left;
            }

            h2 {
                color: #3d4852;
                font-size: 16px;
                font-weight: bold;
                margin-top: 0;
                text-align: left;
            }

            h3 {
                color: #3d4852;
                font-size: 14px;
                font-weight: bold;
                margin-top: 0;
                text-align: left;
            }

            p {
                color: #3d4852;
                font-size: 16px;
                line-height: 1.5em;
                margin-top: 0;
                text-align: left;
            }

            p.sub {
                font-size: 12px;
            }

            img {
                max-width: 100%;
            }

            /* Layout */

            .wrapper {
                background-color: #f8fafc;
                margin: 0;
                padding: 0;
                width: 100%;
                -premailer-cellpadding: 0;
                -premailer-cellspacing: 0;
                -premailer-width: 100%;
            }

            .content {
                margin: 0;
                padding: 0;
                width: 100%;
                -premailer-cellpadding: 0;
                -premailer-cellspacing: 0;
                -premailer-width: 100%;
            }

            /* Header */

            .header {
                background-color: #000000;
                padding: 25px 0;
                text-align: center;
            }

            .header a {
                color: #000000;
                font-size: 19px;
                font-weight: bold;
                text-decoration: none;
                text-shadow: 0 1px 0 white;
            }

            /* Body */

            .body {
                background-color: #ffffff;
                border-bottom: 1px solid #edeff2;
                border-top: 1px solid #edeff2;
                margin: 0;
                padding: 0;
                width: 100%;
                -premailer-cellpadding: 0;
                -premailer-cellspacing: 0;
                -premailer-width: 100%;
            }

            .inner-body {
                background-color: #ffffff;
                margin: 0 auto;
                padding: 0;
                width: 570px;
                -premailer-cellpadding: 0;
                -premailer-cellspacing: 0;
                -premailer-width: 570px;
            }

            /* Subcopy */

            .subcopy {
                border-top: 1px solid #edeff2;
                margin-top: 25px;
                padding-top: 25px;
            }

            .subcopy p {
                font-size: 12px;
            }

            /* Footer */

            .footer {
                background-color: #000000;                
                text-align: center;
                width: 100%;
                -premailer-cellpadding: 0;
                -premailer-cellspacing: 0;
                -premailer-width: 570px;
                position: absolute;
            }

            .footer p {
                color: #ffffff;
                font-size: 12px;
                text-align: center;
            }

            /* Tables */

            .table table {    
                margin: 30px auto;
                width: 100%;
                -premailer-cellpadding: 0;
                -premailer-cellspacing: 0;
                -premailer-width: 100%;
            }

            .table th {
                border-bottom: 1px solid #edeff2;
                padding-bottom: 8px;
                margin: 0;
            }

            .table td {
                color: #74787e;
                font-size: 15px;
                line-height: 18px;
                padding: 10px 0;
                margin: 0;
            }

            .content-cell {
                padding: 35px;
            }

            /* Buttons */

            .action {
                margin: 30px auto;
                padding: 0;
                text-align: center;
                width: 100%;
                -premailer-cellpadding: 0;
                -premailer-cellspacing: 0;
                -premailer-width: 100%;
            }

            .button {
                background-color: #e3342f;
                border-radius: 3px;
                box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);
                color: #ffffff;
                display: inline-block;
                text-decoration: none;
                -webkit-text-size-adjust: none;
            }

            .button-blue,
            .button-primary {
                background-color: #e3342f;
                border-top: 10px solid #e3342f;
                border-right: 18px solid #e3342f;
                border-bottom: 10px solid #e3342f;
                border-left: 18px solid #e3342f;
            }

            .button-green,
            .button-success {
                background-color: #38c172;
                border-top: 10px solid #38c172;
                border-right: 18px solid #38c172;
                border-bottom: 10px solid #38c172;
                border-left: 18px solid #38c172;
            }

            .button-red,
            .button-error {
                background-color: #e3342f;
                border-top: 10px solid #e3342f;
                border-right: 18px solid #e3342f;
                border-bottom: 10px solid #e3342f;
                border-left: 18px solid #e3342f;
            }

            /* Panels */

            .panel {
                margin: 0 0 21px;
            }

            .panel-content {
                background-color: #f1f5f8;
                padding: 16px;
            }

            .panel-item {
                padding: 0;
            }

            .panel-item p:last-of-type {
                margin-bottom: 0;
                padding-bottom: 0;
            }

            /* Promotions */

            .promotion {
                background-color: #ffffff;
                border: 2px dashed #9ba2ab;
                margin: 0;
                margin-bottom: 25px;
                margin-top: 25px;
                padding: 24px;
                width: 100%;
                -premailer-cellpadding: 0;
                -premailer-cellspacing: 0;
                -premailer-width: 100%;
            }

            .promotion h1 {
                text-align: center;
            }

            .promotion p {
                font-size: 15px;
                text-align: center;
            }
            @media only screen and (max-width: 600px) {
                .inner-body {
                    width: 100% !important;
                }

                .footer {
                    width: 100% !important;
                }
            }

            @media only screen and (max-width: 500px) {
                .button {
                    width: 100% !important;
                }
            }
        </style>

        <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td align="center">
                    <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                        <tr>
                            <td class="header">
                                <a >
                                    <div class="footer-brand">
                                        <span class=""><img src="https://contafast.net/sincronizador/public/img/logoQB.png" width="200" alt="Sincronizador QBO-MH"></span>
                                    </div>
                                </a>
                            </td>
                        </tr>

                        <!-- Email Body -->
                        <tr>
                            <td class="body" width="100%" cellpadding="0" cellspacing="0">
                                <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                    <!-- Body content -->
                                    <tr>
                                        <td class="content-cell">

                                            Se adjunta XMLs de factura electrónica con clave: '.$clave.' 

                                            <br>
                                             Este correo fue creado de forma automatica favor no contestar a este.  

                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <table class="footer" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                    <tr>
                                        <td class="content-cell" align="center">
                                            Sincronzador QBO-MH
                                            <br>Todos los derechos reservados.
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
'); 
        $mail->AltBody = 'HTML messaging not supported';
        $mail->addAttachment("files/".$idcard."/Creados/SinEnviar/".$clave."/".$clave.".xml"); //Attach an image file
        $mail->addAttachment( "files/".$idcard."/Creados/SinEnviar/".$clave."/".$clave."-R.xml"); //Attach an image file
        
        if(!$mail->send()){
            echo "Mailer Error: " . $mail->ErrorInfo;
        }else{
            $path = "files/".$idcard."/Creados/SinEnviar/".$clave;
            $pathEnvio = "files/".$idcard."/Creados/Enviados/".$clave;
            if (!file_exists($pathEnvio)) {
                mkdir($pathEnvio, 0755, true);
            }
            rename($path, $pathEnvio);
            echo "Enviado <br>"; 
        }