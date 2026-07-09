<?php
require '../php/conexion.php';

// Verificamos que el token venga en la URL
if (!isset($_GET['token'])) {
    header("Location: login_cliente.php");
    exit();
}

$token = $_GET['token'];

try {
    // Verificamos si el token existe en la base de datos
    $sql = "SELECT correo FROM recuperacion_clave_clientes WHERE token = :token LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':token' => $token]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no existe el token, el enlace es viejo o falso
    if (!$registro) {
        $enlace_valido = false;
    } else {
        $enlace_valido = true;
        $correo_cliente = $registro['correo'];
    }
} catch (PDOException $e) {
    die("Error en el sistema: " . $e->getMessage());
}
?>

<!DOCTYPE html> 
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Contraseña - Pampa Fútbol</title>
    <!-- Vinculamos el nuevo CSS limpio -->
    <link rel="stylesheet" href="../css/estilos_nuevo_password_cliente.css">
</head>
<body>

<div class="reserva-card">
    <h2>ACTUALIZAR CONTRASEÑA</h2>
    <img src="../img/icono-turno.png" alt="Icono" class="icono-turno">

    <?php if (!$enlace_valido): ?>
        <div class="mensaje-error">
            <h3>Enlace Vencido o Inválido</h3>
            <p>El enlace de recuperación ya fue utilizado o no es correcto. Por favor, solicitá uno nuevo.</p>
            <div class="margen-top-15">
                <a href="recuperar_password_cliente.php" class="link-gestion">SOLICITAR OTRO ENLACE</a>
            </div>
        </div>
    <?php else: ?>
        <p class="texto-instrucciones">
            Establecé tu nueva clave de acceso para el correo: <br><b><?= htmlspecialchars($correo_cliente) ?></b>
        </p>

        <form action="../php/actualizar_password_cliente.php" method="POST" autocomplete="off">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="contenedor-formulario-registro">
                <div class="form-group form-group-full">
                    <label>Nueva Contraseña <span class="asterisco">*</span></label>
                    <input type="password" name="nueva_password" required placeholder="Mínimo 6 caracteres" minlength="6">
                </div>
            </div>

            <div class="footer-formulario">
                <div class="gestion-container">
                    <a href="login_cliente.php" class="link-gestion">CANCELAR</a>
                </div>
                <button type="submit" class="btn-enviar">CAMBIAR CONTRASEÑA</button>
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
