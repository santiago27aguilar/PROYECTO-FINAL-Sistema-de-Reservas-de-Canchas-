<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Cliente</title>
    <link rel="stylesheet" href="../css/estilos_login_cliente.css">
</head>
<body>

<div class="login-card">
    <h2>INICIAR SESIÓN</h2>
    <p></p>

    <?php if (isset($_GET['error']) && $_GET['error'] == 'datos_incorrectos'): ?>
        <div class="alerta-error">Correo o contraseña incorrectos</div>
    <?php endif; ?>

    <form action="../php/validar_login_cliente.php" method="POST" autocomplete="off">
        
        <div class="form-group">
            <label>Correo Electrónico <span class="asterisco">*</span></label>
            <input type="email" name="correo" required placeholder="ejemplo@gmail.com" autocomplete="off">
        </div>

        <div class="form-group">
            <label>Contraseña <span class="asterisco">*</span></label>
            <input type="password" name="password" required placeholder="********" autocomplete="new-password">
        </div>

        <button type="submit" class="btn-ingresar">INGRESAR</button>
        
        <div class="links-inferiores">
            <!--<a href="registro_cliente.php">REGISTRATE AQUI</a>-->
            <a href="recuperar_password_cliente.php">OLVIDE MI CONTRASENIA</a>
        </div>
    </form>
</div>

<script src="../js/alerta_cliente.js"></script>

</body>
</html>
