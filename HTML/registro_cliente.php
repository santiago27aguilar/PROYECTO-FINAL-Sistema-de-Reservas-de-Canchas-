<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cliente - Planeta Futbol</title>
    <?php include 'head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_registro_cliente.css">
</head>
<body>

<div class="reserva-card">
    
    <!-- NUEVO CONTENEDOR PARA TÍTULO Y LOGO -->
    <div class="header-registro">
        <h2>CREAR UNA CUENTA</h2>
        <img src="../img/logo-principal-reservas.png" alt="Logo Planeta" class="logo-registro">
    </div>

    <?php if (isset($_GET['error']) && $_GET['error'] == 'existe'): ?>
        <div class="mensaje-error">
            <p>El DNI o el Correo ya están registrados. Intentá iniciar sesión.</p>
        </div>
    <?php endif; ?>

    <form action="../php/procesar_registro_cliente.php" method="POST" autocomplete="off">
        <div class="contenedor-formulario-registro">
            
            <div class="form-row-2">
                <div class="form-group">
                    <label>NOMBRE</label>
                    <input type="text" name="nombre" required  placeholder="Tu nombre">
                </div>
                <div class="form-group">
                    <label>APELLIDO</label>
                    <input type="text" name="apellido" required placeholder="Tu apellido">
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label>DNI - DOCUMENTO</label>
                    <input type="text" name="dni" required placeholder="Sin puntos" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                </div>
                <div class="form-group">
                    <label>WHATSAPP</label>
                    <input type="tel" name="telefono" required placeholder="381..." oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label>CORREO ELECTRONICO</label>
                    <input type="email" name="correo" required placeholder="ejemplo@gmail.com" autocomplete="nope">
                </div>
                <div class="form-group">
                    <label>ELEGIR CONTRASEÑA</label>
                    <input type="password" name="password" required placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                </div>
            </div>

            <!--<span class="asterisco">Campo Obligatorio</span>-->

        </div>

        <div class="footer-formulario">
            <div class="gestion-container">
                <a href="login_cliente.php" class="link-gestion">¿YA TIENES UNA CUENTA? INICIA SESION AQUI</a>
            </div>
            <button type="submit" class="btn-enviar">REGISTRAR CUENTA</button>
        </div>

    </form>
</div>

</body>
</html>
