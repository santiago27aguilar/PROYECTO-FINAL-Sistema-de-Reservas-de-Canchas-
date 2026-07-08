<?php
session_start();
include 'conexion.php';

// Cargamos el cartero (PHPMailer) al principio del archivo
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    try {
        // Buscamos al cliente por su correo
        $sql = "SELECT idclientes, nombre, apellido, password FROM clientes WHERE correo = :correo";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificamos si la contraseña ingresada coincide con la encriptada en la BD
            if (password_verify($password, $cliente['password'])) {
                
                // ¡Éxito! Creamos las variables de sesión
                $_SESSION['id_cliente'] = $cliente['idclientes']; 
                $_SESSION['cliente_nombre'] = $cliente['nombre'];
                $_SESSION['cliente_apellido'] = $cliente['apellido'];
                
                // --- INICIO DEL ENVÍO DE NOTIFICACIÓN DE SEGURIDAD ---
                $mail = new PHPMailer(true);
                
                // ¡ACÁ ESTÁ LA SOLUCIÓN! Esta línea arregla los acentos y las ñ:
                $mail->CharSet = 'UTF-8';
                
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    
                    // Tus credenciales
                    $mail->Username = 'santiagaguilardecano@gmail.com'; 
                    $mail->Password = 'pbsp tfaw iolq wuwp'; // Volvé a poner tu contraseña de aplicación de Gmail acá
                    
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('santiagaguilardecano@gmail.com', 'Pampa Fútbol');
                    $mail->addAddress($correo); // Enviamos al mail con el que intentó loguearse
                    $mail->isHTML(true);
                    $mail->Subject = 'Nuevo inicio de sesion detectado';

                    // Ajustamos la zona horaria para que el mail tenga la hora exacta
                    date_default_timezone_set('America/Argentina/Tucuman');
                    $fecha_hora = date('d/m/Y H:i:s');

                    $mail->Body = "Hola <b>" . $cliente['nombre'] . "</b>,<br><br>
                                   Hemos detectado un nuevo inicio de sesión en tu cuenta el <b>$fecha_hora</b>.<br><br>
                                   Si fuiste tú, podés ignorar este mensaje. Si no reconocés esta actividad, por favor utilizá la opción de recuperar contraseña inmediatamente para proteger tu cuenta.<br><br>
                                   Saludos del equipo.";

                    $mail->send();
                } catch (Exception $e) {
                    // Si el mail falla (por ej, sin internet), el catch vacío permite que 
                    // el cliente inicie sesión de todas formas sin ver un error feo en pantalla.
                }
                // --- FIN DEL ENVÍO DE NOTIFICACIÓN ---

                // Le abrimos la puerta al formulario de reservas
                header("Location: ../html/cliente.php");
                exit();
            } else {
                header("Location: ../html/login_cliente.php?error=datos_incorrectos");
                exit();
            }
        } else {
            header("Location: ../html/login_cliente.php?error=datos_incorrectos");
            exit();
        }
    } catch (PDOException $e) {
        echo "Error en el login: " . $e->getMessage();
    }
}
?>
