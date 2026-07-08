<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargamos el cartero (PHPMailer) y la conexión a la base de datos
require '../vendor/autoload.php'; 
require 'conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];

    // Buscamos si el usuario existe
    $stmt = $conexion->prepare("SELECT idusuario, nombre FROM usuario WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Generamos el token y la fecha de expiración
        $token = bin2hex(random_bytes(32));
        date_default_timezone_set('America/Argentina/Tucuman');
        $expiracion = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Guardamos en la base de datos
        $stmt_insert = $conexion->prepare("INSERT INTO recuperacion_de_clave_usuario (correo, token, expiracion) VALUES (:correo, :token, :expiracion)");
        $stmt_insert->execute([':correo' => $correo, ':token' => $token, ':expiracion' => $expiracion]);

        // Configuración para enviar el email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            
            // --- ACÁ PONÉS TUS DATOS ---
            $mail->Username = 'santiagaguilardecano@gmail.com'; 
            $mail->Password = 'pbsp tfaw iolq wuwp'; 
            // ---------------------------

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('santiagaguilardecano@gmail.com', 'Sistema de Reservas');
            $mail->addAddress($correo);
            $mail->isHTML(true);
            $mail->Subject = 'Recuperacion de contrasenia';
            $mail->Body    = 'Hola, haz clic en el siguiente enlace para recuperar tu contraseña: <br> 
                              <a href="http://localhost/Gestion de Reservas de Canchas/html/nuevo_password_usuario.php?token='.$token.'">Recuperar cuenta</a>';

            $mail->send();
            header("Location: ../html/recuperar_password_usuario.php?exito=1");
        } catch (Exception $e) {
            echo "Error al enviar el mail: {$mail->ErrorInfo}";
        }
    } else {
        header("Location: ../html/recuperar_password_usuario.php?error=1");
    }
}
?>
