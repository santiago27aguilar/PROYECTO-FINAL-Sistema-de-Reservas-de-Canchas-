<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Cuenta - Gestión de Canchas</title>
    <link rel="stylesheet" href="../css/estilos_recuperar_password_usuario.css">
</head>
<body>
    <div class="recover-container">
        <div class="recover-card">

            <div class="logo-container">
                <div class="circular-placeholder">
                    <img src="../img/contrasenia.png" alt="Futbol">
                </div>
                <div class="circular-placeholder">
                    <img src="../img/nueva-contrasenia.png" alt="Padel">
                </div>
            </div>

            <h1>RECUPERAR CONTRASENIA</h1>
            <p>Ingresa tu correo electrónico para recuperar tu cuenta</p>

            <!-- NUEVO: Cartel de Error con variable de Sesión -->
            <?php if(isset($_SESSION['error_recuperacion'])): ?>
                <div class="error-msg">
                    <?= htmlspecialchars($_SESSION['error_recuperacion']) ?>
                </div>
                <?php unset($_SESSION['error_recuperacion']); // Borramos el error ?>
            <?php endif; ?>

            <!-- NUEVO: Cartel de Éxito con variable de Sesión -->
            <?php if(isset($_SESSION['exito_recuperacion'])): ?>
                <div class="success-msg">
                    <?= htmlspecialchars($_SESSION['exito_recuperacion']) ?>
                </div>
                <?php unset($_SESSION['exito_recuperacion']); // Borramos el éxito ?>
            <?php endif; ?>

            <form action="../php/procesar_recuperacion_usuario.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <label>CORREO ELECTRÓNICO</label>
                    <input type="email" name="correo" placeholder="ejemplo@correo.com" required>
                </div>

                <button type="submit" class="btn-primary">CONTINUAR</button>
            </form>

            <div class="footer-link">
                <a href="login.php">VOLVER AL INICIO</a>
            </div>

        </div>
    </div>

    <script src="../js/alerta_cliente.js"></script>
</body> 
</html>
