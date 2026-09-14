<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Llamamos al autoload de Composer
require '../vendor/autoload.php';

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- EFECTO DE CARGA ---
    sleep(4);
    // -----------------------

    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Encriptamos la contraseña
    $password_hasheada = password_hash($password, PASSWORD_DEFAULT);
    
    // Generamos el token nuevamente porque lo querés mostrar en el correo
    $token = bin2hex(random_bytes(16));

    try {
        $check = $conexion->prepare("SELECT idclientes FROM clientes WHERE dni = :dni OR correo = :correo");
        $check->execute([':dni' => $dni, ':correo' => $correo]);

        if ($check->rowCount() > 0) {
            header("Location: ../html/registro_cliente.php?error=existe");
            exit();
        }

        // Insertamos el cliente (incluyendo el token y dejándolo Activo por defecto)
        $sql = "INSERT INTO clientes (nombre, apellido, dni, telefono, correo, password, estado, token) VALUES (:nombre, :apellido, :dni, :telefono, :correo, :password, 'Activo', :token)";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':dni' => $dni,
            ':telefono' => $telefono,
            ':correo' => $correo,
            ':password' => $password_hasheada,
            ':token' => $token
        ])) {
            
            // --- INICIO ENVÍO DE CORREO DE BIENVENIDA CON TOKEN ---
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                
                // TU CORREO Y TU CLAVE DE APLICACIÓN
                $mail->Username   = 'tu_correo_aqui@gmail.com'; // Escribí tu correo real
                $mail->Password   = 'ihlucsjjmqdjnybc';         // Tu clave generada
                
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Remitente y destinatario
                $mail->setFrom('tu_correo_aqui@gmail.com', 'Pampa Futbol');
                $mail->addAddress($correo, $nombre . ' ' . $apellido);

                // Contenido del correo mostrando solo el token
                $mail->isHTML(true);
                $mail->Subject = 'Cuenta creada exitosamente - Tu Token';
                
                $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 10px;'>
                    <h2 style='color: #333;'>¡Hola $nombre!</h2>
                    <p style='font-size: 16px; color: #555;'>Tu cuenta en el sistema de reservas de <b>Pampa Fútbol</b> ha sido creada con éxito.</p>
                    <p style='font-size: 16px; color: #555;'>Tu código (token) de seguridad asociado a tu cuenta es el siguiente:</p>
                    
                    <div style='background-color: #f4f4f4; padding: 15px; text-align: center; font-size: 18px; font-weight: bold; letter-spacing: 1px; border-radius: 5px; margin: 20px 0; color: #28a745;'>
                        $token
                    </div>
                    
                    <p style='font-size: 16px; color: #555;'>Guardá este código, ya que el sistema podría solicitártelo más adelante para gestionar tus reservas de canchas.</p>
                    <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #999;'>Este es un mensaje automático. Si no fuiste vos quien creó esta cuenta, por favor ignorá este correo.</p>
                </div>";

                $mail->send();
            } catch (Exception $e) {
                // Si falla el mail, el cliente se registra igual
            }
            // --- FIN ENVÍO DE CORREO ---

            header("Location: ../html/login_cliente.php?mensaje=registrado");
            exit();
        }
    } catch (PDOException $e) {
        echo "Error al registrar: " . $e->getMessage();
    }
}
?>
