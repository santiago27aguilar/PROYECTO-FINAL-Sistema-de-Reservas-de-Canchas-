<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planeta de Fútbol - Inicio</title>
    <link rel="stylesheet" href="../css/estilos_landingpage.css">
    <!-- FontAwesome para los íconos (incluye la hamburguesa) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- CABECERA -->
    <header class="navbar-landing">
        <div class="logo">
            <h1>PLANETA - FUTBOL <i class="fas fa-futbol"></i> <i class="fas fa-table-tennis-paddle-ball"></i></h1>
        </div>
        
        <!-- Ícono de Hamburguesa (Solo visible en celulares) -->
        <div class="menu-toggle" id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>

        <!-- Contenedor de Botones -->
        <div class="botones-auth" id="nav-links">
            <a href="login_cliente.php" class="btn-auth">INICIAR SESION</a>
            <a href="registro_cliente.php" class="btn-auth">CREAR CUENTA</a>
        </div>
    </header>

    <!-- BANNER PRINCIPAL (Fondo y Título separados) -->
    <section class="contenedor-banner">
        <!-- 1. Solo la foto -->
        <div class="banner-cancha"></div>
        
        <!-- 2. Solo el cuadro de texto (ahora va debajo, sin tapar la imagen) -->
        <div class="banner-overlay">
            <h2 class="titulo-complejo">Planeta de Futbol - Canchas de FUTBOL 5, FUTBOL 7 Y PADEL</h2>
            <p><i class="fas fa-location-dot"></i> Ubicacion, Tucuman</p>
        </div>
    </section>

    <!-- SECCIÓN: MAPA E INFORMACIÓN -->
    <section class="info-complejo">
        <h3 class="titulo-seccion">¿DONDE ENCONTRARNOS?</h3>
        
        <div class="grid-info">
            <!-- Columna Izquierda: Mapa de Google -->
            <div class="mapa-container">
                <iframe 
                    class="mapa-iframe"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3560.123456789!2d-65.2071!3d-26.8300!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjbCsDQ5JzQ4LjAiUyA2NcKwMTInMjUuNiJX!5e0!3m2!1ses!2sar!4v1620000000000!5m2!1ses!2sar" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>

            <!-- Columna Derecha: Tarjetas de Datos -->
            <div class="datos-club">
                <!-- Tarjeta Horarios -->
                <div class="tarjeta-verde">
                    <h4><i class="fas fa-clock"></i> HORARIOS</h4>
                    <p>Lunes a Domingo: 14:00 a 00:00 hs</p>
                </div>
                
                <!-- Tarjeta Ubicación -->
                <div class="tarjeta-verde">
                    <h4><i class="fas fa-map-location-dot"></i> UBICACION</h4>
                    <p>Ubicacion, Tucuman</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer-landing">
        <p>&copy; <?php echo date('Y'); ?> Planeta de Futbol</p>
    </footer>

    <script src="../js/menu_desplegable.js"></script>
    
</body>
</html>
