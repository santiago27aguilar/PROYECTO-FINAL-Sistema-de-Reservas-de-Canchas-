<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Clave - Pampa Fútbol</title>
    <link rel="stylesheet" href="../css/estilos_recuperar_password_cliente.css"> 
</head>
<body>

    <div class="recuperar-container">
        <div class="recuperar-card">
            
            <h2>RECUPERAR CLAVE</h2>
            <p class="texto-explicativo">Ingresá tu correo y te enviaremos un enlace para cambiar tu contraseña.</p>

            <?php if(isset($_GET['error'])): ?>
                <div class="alerta-error">El correo no está registrado en el sistema.</div>
            <?php endif; ?>
            
            <?php if(isset($_GET['exito'])): ?>
                <div class="alerta-exito">¡Listo! Te enviamos un enlace a tu correo.</div>
            <?php endif; ?>

            <form action="../php/procesar_recuperacion_cliente.php" method="POST">
                <div class="form-group">
                    <label>Correo Electrónico <span class="asterisco">*</span></label>
                    <input type="email" name="correo" placeholder="ejemplo@gmail.com" required>
                </div>
                
                <button type="submit" class="btn-enviar">ENVIAR ENLACE</button>
            </form>

            <div class="forgot-link">
                <a href="login_cliente.php">VOLVER AL INICIO</a>
            </div>

        </div>
    </div>

</body>
</html>
