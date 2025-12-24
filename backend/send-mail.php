<?php

$config = require __DIR__ . '/smtp-config.php';

/* =============================
   Funciones SMTP (IONOS)
   ============================= */

function smtp_read($conn) {
    $response = '';
    while ($line = fgets($conn, 515)) {
        $response .= $line;
        if (preg_match('/^\d{3} /', $line)) break;
    }
    return $response;
}

function smtp_cmd($conn, $cmd, $expect) {
    fwrite($conn, $cmd);
    $resp = smtp_read($conn);
    return strpos($resp, (string)$expect) === 0 ? $resp : false;
}

function sendSMTP($to, $subject, $body, $config) {
    $conn = fsockopen("ssl://".$config['host'], $config['port'], $errno, $errstr, 15);
    if (!$conn) return ['status'=>'error','message'=>"No se pudo conectar: $errstr"];

    smtp_read($conn);

    $steps = [
        ["EHLO analytee.com\r\n", 250],
        ["AUTH LOGIN\r\n", 334],
        [base64_encode($config['username'])."\r\n", 334],
        [base64_encode($config['password'])."\r\n", 235],
        ["MAIL FROM:<{$config['from_email']}>\r\n", 250],
        ["RCPT TO:<$to>\r\n", 250],
        ["DATA\r\n", 354],
    ];

    foreach ($steps as [$cmd,$exp]) {
        if (!smtp_cmd($conn,$cmd,$exp)) {
            return ['status'=>'error','message'=>"Error SMTP en $cmd"];
        }
    }

    $headers =
        "From: Analytee <{$config['from_email']}>\r\n" .
        "Reply-To: {$config['from_email']}\r\n" .
        "MIME-Version: 1.0\r\n" .
        "Content-Type: text/plain; charset=UTF-8\r\n";

    fwrite(
        $conn,
        "To: $to\r\n" .
        "Subject: $subject\r\n" .
        $headers . "\r\n" .
        $body . "\r\n.\r\n"
    );

    if (!smtp_cmd($conn, "", 250)) {
        return ['status'=>'error','message'=>"IONOS rechazó el mensaje"];
    }

    fwrite($conn, "QUIT\r\n");
    fclose($conn);

    return ['status'=>'ok','message'=>'Mensaje enviado exitosamente'];
}

/* =============================
   PROCESAR FORMULARIO
   ============================= */

$nombre       = trim($_POST['nombre'] ?? '');
$email        = trim($_POST['email'] ?? '');
$negocio      = trim($_POST['negocio'] ?? '');
$googleMaps   = trim($_POST['google_maps'] ?? '');
$mensaje      = trim($_POST['mensaje'] ?? '');
$rgpd         = isset($_POST['rgpd']);

/* Captura robusta del campo URL del negocio (acepta cualquier variante) */
$urlNegocio = trim(
    $_POST['business_url'] ??
    $_POST['negocio_url'] ??
    $_POST['url'] ??
    $_POST['website'] ??
    $_POST['web'] ??
    ''
);

/* Validación de URL */
if ($urlNegocio !== '' && !filter_var($urlNegocio, FILTER_VALIDATE_URL)) {
    $urlNegocio = "URL inválida enviada por el usuario";
}

if ($nombre === '' || $email === '' || $negocio === '' || !$rgpd) {
    echo json_encode(['status'=>'error','message'=>'Campos obligatorios incompletos']);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

$body =
"📧 Nuevo Mensaje de Contacto\n\n" .
"Has recibido un mensaje desde el formulario de Analytee.\n\n" .
"Nombre:\n$nombre\n\n" .
"Email:\n$email\n\n" .
"Negocio:\n$negocio\n\n" .
"URL del Negocio:\n" . ($urlNegocio ?: "No proporcionada") . "\n\n" .
"URL Google Maps:\n" . ($googleMaps ?: "No proporcionada") . "\n\n" .
"Mensaje:\n" . ($mensaje ?: "Sin mensaje adicional") . "\n\n" .
"Información adicional:\n" .
"Fecha: " . date('Y-m-d H:i:s') . "\n" .
"IP: $ip\n" .
"User Agent: $ua\n";

$result = sendSMTP(
    "contacto@analytee.com",
    "Nueva solicitud - $negocio",
    $body,
    $config
);

header('Content-Type: application/json');
echo json_encode($result);
