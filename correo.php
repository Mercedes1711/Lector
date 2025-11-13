<?php
include 'conexion_bd.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; //  Carga automática de PHPMailer
// Configuración del correo
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'Tristalia11@gmail.com'); 
define('SMTP_PASS', 'jxbs tigv qnuo ckpn');    
define('SMTP_FROM_NAME', 'Miniportal Soporte');


function enviarCorreo($destinatario, $nombre, $asunto, $cuerpoHTML) {
    // Validar parámetros
    if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
        return ['exito' => false, 'mensaje' => 'Correo electrónico inválido'];
    }

    if (empty($nombre) || empty($asunto) || empty($cuerpoHTML)) {
        return ['exito' => false, 'mensaje' => 'Todos los campos son requeridos'];
    }

    try {
        $mail = new PHPMailer(true);

        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        
        // Configuración de debug (comentar en producción)
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        // Remitente y destinatario
        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($destinatario, $nombre);
        
        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpoHTML;
        $mail->AltBody = strip_tags($cuerpoHTML);
        
        // Enviar
        $mail->send();
        return ['exito' => true, 'mensaje' => 'Cuenta creada correctamente'];
        
    } catch (Exception $e) {
        error_log("Error al enviar correo a {$destinatario}: {$mail->ErrorInfo}");
        return [
            'exito' => false, 
            'mensaje' => 'Error al enviar el correo',
            'error_detalle' => $mail->ErrorInfo // Solo en desarrollo
        ];
    }
}
?>