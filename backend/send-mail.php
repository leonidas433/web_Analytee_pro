<?php

$config = require __DIR__ . '/smtp-config.php';

// Configuración de logs y timeouts
define('SMTP_LOG_FILE', __DIR__ . '/logs/mail_errors.log');
define('SMTP_TIMEOUT', 10);

function log_smtp_error($msg) {
    $date = date('Y-m-d H:i:s');
    $logMsg = "[$date] $msg" . PHP_EOL;
    error_log($logMsg, 3, SMTP_LOG_FILE);
}

/* =============================
   Funciones SMTP (IONOS)
   ============================= */

function smtp_read($conn) {
    $response = '';
    $info = stream_get_meta_data($conn);
    
    while (!feof($conn) && !$info['timed_out']) {
        $line = fgets($conn, 515);
        $info = stream_get_meta_data($conn);
        
        if ($info['timed_out']) {
            log_smtp_error("SMTP Read Timeout");
            return false;
        }
        
        $response .= $line;
        if (preg_match('/^\d{3} /', $line)) break;
    }
    return $response;
}

function smtp_cmd($conn, $cmd, $expect) {
    if ($cmd !== "") {
        fwrite($conn, $cmd);
    }
    $resp = smtp_read($conn);
    
    if ($resp === false) return false;
    
    if (strpos($resp, (string)$expect) !== 0) {
        // Log solo si no es el cierre de conexión normal
        if ($cmd !== "QUIT\r\n") {
            log_smtp_error("SMTP Error. CMD: " . trim($cmd) . " | RESP: " . trim($resp));
        }
        return false;
    }
    return $resp;
}

function sendSMTP($to, $subject, $body, $config) {
    $conn = fsockopen("ssl://".$config['host'], $config['port'], $errno, $errstr, SMTP_TIMEOUT);
    
    if (!$conn) {
        log_smtp_error("Connection failed: $errstr ($errno)");
        return ['status'=>'error','message'=>"No se pudo conectar al servidor de correo"];
    }

    stream_set_timeout($conn, SMTP_TIMEOUT);

    $initial = smtp_read($conn);
    if ($initial === false) {
        fclose($conn);
        return ['status'=>'error','message'=>"Timeout esperando saludo SMTP"];
    }

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
        if (smtp_cmd($conn,$cmd,$exp) === false) {
            fclose($conn);
            return ['status'=>'error','message'=>"Error técnico enviando correo"];
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

    if (smtp_cmd($conn, "", 250) === false) {
        log_smtp_error("Mensaje rechazado tras envío de DATA");
        fclose($conn);
        return ['status'=>'error','message'=>"El servidor de correo rechazó el mensaje"];
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
