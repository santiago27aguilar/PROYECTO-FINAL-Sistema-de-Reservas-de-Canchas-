<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Pampa Fútbol</title>
    <link rel="stylesheet" href="../css/estilos_recuperar_password.css">
</head>
<body>

<div class="reserva-card">
    <h2>RECUPERAR TU CUENTA</h2>
    
    <p class="texto-explicativo">
        Ingresá tu correo electrónico y te enviaremos un enlace para generar una nueva contraseña.
    </p>

    <?php if (isset($_GET['error']) && $_GET['error'] == 'no_existe'): ?>
        <div class="mensaje-error">
            <p>El correo ingresado no se encuentra registrado en el sistema.</p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['exito']) && $_GET['exito'] == '1'): ?>
        <div class="mensaje-exito">
            <h3>¡Enlace Enviado!</h3>
            <p>Revisá tu casilla de correo (o la carpeta de spam) para restablecer tu clave.</p>
        </div>
    <?php endif; ?>

    <form action="../php/procesar_recuperacion_cliente.php" method="POST" autocomplete="off">
        <div class="contenedor-formulario-registro">
            <div class="form-group form-group-full">
                <label>Correo Electrónico <span class="asterisco">*</span></label>
                <input type="email" name="correo" required placeholder="ejemplo@gmail.com">
            </div>
        </div>

        <div class="footer-formulario">
            <div class="gestion-container">
                <a href="login_cliente.php" class="link-gestion">VOLVER AL INICIO DE SESIÓN</a>
            </div>
            <button type="submit" class="btn-enviar">ENVIAR ENLACE</button>
        </div>
    </form>
</div>

</body>
</html>
