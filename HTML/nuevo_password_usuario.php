<?php
// nueva_password.php
require '../php/conexion.php';

// Validamos que el token exista en la URL y sea válido
$token = $_GET['token'] ?? '';

$stmt = $conexion->prepare("SELECT correo FROM recuperacion_de_clave_usuario WHERE token = :token AND expiracion > NOW()");
$stmt->execute([':token' => $token]);
$valido = $stmt->fetch();

// Si el token no es válido o ya expiró, lo enviamos de vuelta al login
if (!$valido) {
    header("Location: login.php?error=token_invalido");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Gestión de Canchas</title>
    <link rel="stylesheet" href="../css/estilos_nuevo_password_usuario.css">
</head>
<body>
    <div class="recover-container">
        <div class="recover-card">

            <div class="logo-container">
                <div class="circular-placeholder">
                    <img src="../img/contrasenia.png" alt="Seguridad">
                </div>
                <div class="circular-placeholder">
                    <img src="../img/nueva-contrasenia.png" alt="Nueva Clave">
                </div>
            </div>

            <h1>ACTUALIZAR NUEVA CONTRASENIA</h1>
            <p>Establece tu nueva clave de acceso</p>

            <form action="../php/actualizar_password_usuario.php" method="POST" autocomplete="off">
                
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="form-group">
                    <label>NUEVA CLAVE</label>
                    <input type="password" name="nueva_pass" placeholder="••••••••" required minlength="6">
                </div>

                <button type="submit" class="btn-primary">CAMBIAR CONTRASENIA</button>
            </form>

            <div class="footer-link">
                <a href="login.php">VOLVER AL INICIO</a>
            </div>

        </div>
    </div>
</body> 
</html>
