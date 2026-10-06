<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planeta de Fútbol - Inicio</title>
    <?php include 'head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_landingpage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- CABECERA -->
    <header class="navbar-landing">
        <div class="logo">
            <h1>
                <img src="../img/logo-principal-reservas.png" alt="Logo Planeta" class="logo-navbar">
            </h1>
        </div>
        
        <!-- Ícono de Hamburguesa (Solo visible en celulares) -->
        <div class="menu-toggle" id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>

        <!-- Contenedor de Botones -->
        <div class="botones-auth" id="nav-links">
            <a href="como_funciona.php" class="btn-auth">¿CÓMO FUNCIONA?</a>
            <a href="login_cliente.php" class="btn-auth">INICIAR SESION</a>
            <a href="registro_cliente.php" class="btn-auth">CREAR CUENTA</a>
        </div>
    </header>

    <!-- BANNER PRINCIPAL -->
    <section class="contenedor-banner">
        <div class="banner-cancha">
            <img src="../img/logo-principal-reservas.png" alt="Marca de Agua" class="logo-watermark">
        </div>
        
        <div class="banner-overlay">
            <div class="texto-banner">
                <h2 class="titulo-complejo">Complejo de Canchas de FUTBOL 5, FUTBOL 7 Y PADEL</h2>
                <p><i class="fas fa-location-dot"></i> Ubicacion, Tucuman</p>
            </div>
            
            <a href="login_cliente.php" class="btn-reservar-banner">
                <i class="fas fa-calendar-check"></i> ¡RESERVAR MI CANCHA AHORA!
            </a>
        </div>
    </section>

    <!-- SECCIÓN: MAPA E INFORMACIÓN -->
    <section class="info-complejo" id="ubicacion-horarios">
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
                <div class="tarjeta-verde">
                    <h4><i class="fas fa-clock"></i> HORARIOS</h4>
                    <p>Lunes a Domingo: 14:00 a 00:00 hs</p>
                </div>
                
                <div class="tarjeta-verde">
                    <h4><i class="fas fa-map-location-dot"></i> UBICACION</h4>
                    <p>Ubicacion, Tucuman</p>
                </div>
                
                <div class="tarjeta-verde">
                    <h4><i class="fas fa-star"></i> INSTALACIONES</h4>
                    <p>Buffet, Vestuarios, Iluminación LED y Estacionamiento.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER MODERNO (Centrado) -->
    <footer class="footer-landing">
        <div class="footer-contenido">
            <!-- Columna 1: Marca -->
            <div class="footer-col">
                <h4>PLANETA FUTBOL</h4>
                <p>El mejor complejo deportivo de Tucumán para disfrutar del fútbol y pádel con amigos. ¡Te esperamos para el tercer tiempo!</p>
            </div>
            
            <!-- Columna 2: Enlaces -->
            <div class="footer-col">
                <h4>ENLACES RÁPIDOS</h4>
                <ul>
                    <li><a href="como_funciona.php">¿Cómo Funciona?</a></li>
                    <li><a href="login_cliente.php">Iniciar Sesión</a></li>
                    <li><a href="registro_cliente.php">Crear Cuenta</a></li>
                    <li><a href="#ubicacion-horarios">Ubicación y Horarios</a></li>
                </ul>
            </div>
            
            <!-- Columna 3: Redes y Contacto -->
            <div class="footer-col">
                <h4>SÍGUENOS</h4>
                <div class="redes-iconos">
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
                <p class="footer-contacto"><i class="fas fa-phone"></i> +54 9 XXX XXX XXXX</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Planeta de Futbol. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- BOTÓN FLOTANTE DE WHATSAPP -->
    <a href="https://wa.me/5493814152422?text=Hola,%20quiero%20hacer%20una%20consulta%20por%20las%20canchas" class="btn-whatsapp-flotante" target="_blank" title="¡Escribenos por WhatsApp!">
        <i class="fab fa-whatsapp"></i>
    </a>

    <script src="../js/menu_desplegable.js"></script>
</body>
</html>
