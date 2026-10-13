<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login de Usuarios - Planeta de Futbol</title>
    <?php include 'head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card"> 
            
            <div class="left-side">
                <div class="img-wrapper">
                    <img src="../img/logo-principal-reservas.png" alt="Planeta Fútbol y Pádel">
                </div>
            </div>

            <div class="right-side">
                
                <div class="header-admin">
                    <h1>CONTROL DE ACCESO</h1>
                    <img src="../img/logo-principal-reservas.png" alt="Logo Planeta" class="logo-admin-mobile">
                </div>
                
                <p>INGRESA TUS DATOS PARA PODER CONTINUAR</p>

                <?php if (isset($_SESSION['error_login'])): ?>
                    <div class="error-msg"><?= htmlspecialchars($_SESSION['error_login']) ?></div>
                    <?php 
                        unset($_SESSION['error_login']); 
                    ?>
                <?php endif; ?>
                
                <form action="../php/validar_login.php" method="POST" autocomplete="off">
                    <div class="form-group">
                        <label>USUARIO</label>
                        <input type="text" name="user" autocomplete="off" placeholder="Nombre de usuario" required>
                    </div>
                    
                    <div class="form-group">
                        <label>CONTRASEÑA</label>
                        <input type="password" name="pass" autocomplete="new-password" placeholder="••••••••" required>
                    </div>
                    
                    <button type="submit" class="btn-login">ACCEDER AL INICIO</button>
                    
                    <div class="forgot-link">
                        <a href="recuperar_password_usuario.php" class="link-forgot">¿OLVIDASTE TU CONTRASEÑA?</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</body> 
</html>
