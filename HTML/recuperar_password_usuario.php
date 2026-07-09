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

            <?php if(isset($_GET['error'])): ?>
                <div class="error-msg">
                    El correo electrónico no está registrado.
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['exito'])): ?>
                <div class="success-msg">
                    Revisa tu bandeja de entrada. Te enviamos un enlace.
                </div>
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
