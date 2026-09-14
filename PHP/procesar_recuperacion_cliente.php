<?php
session_start(); // NUEVO: Iniciamos sesión para guardar los mensajes

require 'conexion.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);

    try {
        // 1. Verificamos si el correo existe en la tabla clientes
        $sql = "SELECT idclientes, nombre FROM clientes WHERE correo = :correo";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':correo' => $correo]);

        if ($stmt->rowCount() == 1) {
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 2. Generamos un token seguro y único
            $token = bin2hex(random_bytes(50));
            date_default_timezone_set('America/Argentina/Tucuman');
            $fecha_creacion = date('Y-m-d H:i:s');

            // 3. Guardamos el token en la base de datos
            $sql_token = "INSERT INTO recuperacion_de_clave_clientes (correo, token, fecha_de_creacion) VALUES (:correo, :token, :fecha)";
            $stmt_token = $conexion->prepare($sql_token);
            $stmt_token->execute([
                ':correo' => $correo,
                ':token' => $token,
                ':fecha' => $fecha_creacion
            ]);

            // 4. Armamos el link de recuperación
            $enlace = "http://localhost/Gestion%20de%20Reservas%20de%20Canchas/html/nuevo_password_cliente.php?token=" . $token;

            // 5. Enviamos el correo con PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->CharSet = 'UTF-8';
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'santiagaguilardecano@gmail.com'; 
                $mail->Password = 'jnwqlswjeofwzkmk'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('santiagaguilardecano@gmail.com', 'Pampa Futbol');
                $mail->addAddress($correo);
                $mail->isHTML(true);
                $mail->Subject = 'Recuperar Contraseña - Pampa Futbol';
                $mail->Body = "Hola <b>" . $cliente['nombre'] . "</b>,<br><br>
                               Solicitaste restablecer tu contraseña. Hacé clic en el siguiente enlace para crear una nueva:<br><br>
                               <a href='$enlace'>$enlace</a><br><br>
                               Si no solicitaste este cambio, podés ignorar este correo.<br><br>
                               Saludos del equipo.";

                $mail->send();
                
                // NUEVO: Guardamos el éxito en la sesión y redirigimos limpio
                $_SESSION['exito_rec_cliente'] = "¡Listo! Te enviamos un enlace a tu correo.";
                header("Location: ../html/recuperar_password_cliente.php");
                exit();
            } catch (Exception $e) {
                die("Error al enviar el correo: " . $mail->ErrorInfo);
            }
        } else {
            // NUEVO: Guardamos el error en la sesión y redirigimos limpio
            $_SESSION['error_rec_cliente'] = "El correo no está registrado en el sistema.";
            header("Location: ../html/recuperar_password_cliente.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Error de Base de Datos: " . $e->getMessage());
    }
} else {
    header("Location: ../html/recuperar_password_cliente.php");
    exit();
}
?>
