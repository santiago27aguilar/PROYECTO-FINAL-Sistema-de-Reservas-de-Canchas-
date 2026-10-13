<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Clave del Cliente - Planeta de Futbol</title>
    <?php include 'head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_recuperar_password_cliente.css?v=2"> 
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

            <h1>RECUPERA TU CONTRASEÑA</h1>
            <p>Ingresa tu correo electrónico para recuperar tu contraseña</p>

            <!-- Leemos el error desde la sesión y lo borramos -->
            <?php if(isset($_SESSION['error_rec_cliente'])): ?>
                <div class="error-msg">
                    <?= htmlspecialchars($_SESSION['error_rec_cliente']) ?>
                </div>
                <?php unset($_SESSION['error_rec_cliente']); ?>
            <?php endif; ?>
            
            <!-- Leemos el éxito desde la sesión y lo borramos -->
            <?php if(isset($_SESSION['exito_rec_cliente'])): ?>
                <div class="success-msg">
                    <?= htmlspecialchars($_SESSION['exito_rec_cliente']) ?>
                </div>
                <?php unset($_SESSION['exito_rec_cliente']); ?>
            <?php endif; ?>

            <form action="../php/procesar_recuperacion_cliente.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <label>CORREO ELECTRÓNICO</label>
                    <input type="email" name="correo" placeholder="ejemplo@correo.com" required>
                </div>
                
                <button type="submit" class="btn-primary">CONTINUAR</button>
            </form>

            <div class="footer-link">
                <a href="login_cliente.php">VOLVER AL INICIO</a>
            </div>

        </div>
    </div>
</body>
</html>
