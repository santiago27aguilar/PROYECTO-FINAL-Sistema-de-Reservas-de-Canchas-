<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cómo Funciona - Planeta de Fútbol</title>
    <?php include 'head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_como_funciona.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- CABECERA EXACTA AL LANDING PAGE -->
    <header class="navbar-landing">
        <div class="logo">
            <h1>
                <a href="landingpage.php">
                    <img src="../img/logo-principal-reservas.png" alt="Logo Planeta" class="logo-navbar">
                </a>
            </h1>
        </div>
        
        <div class="menu-toggle" id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>

        <div class="botones-auth" id="nav-links">
            <a href="como_funciona.php" class="btn-auth">¿CÓMO FUNCIONA?</a>
            <a href="login_cliente.php" class="btn-auth">INICIAR SESION</a>
            <a href="registro_cliente.php" class="btn-auth">CREAR CUENTA</a>
        </div>
    </header>

    <!-- SECCIÓN PRINCIPAL: GUÍA PASO A PASO -->
    <section class="seccion-como-funciona">
        <div class="titulo-guia-container">
            <span class="badge-guia">Guía Paso a Paso</span>
            <h2>¿Cómo reservar en Planeta de Fútbol?</h2>
            <p>Todo el proceso desde que ingresás a la plataforma hasta que asegurás tu turno en la cancha.</p>
        </div>

        <!-- CONTENEDOR 2x2 -->
        <div class="grid-pasos-guia">
            
            <!-- PASO 01 -->
            <div class="tarjeta-paso-guia">
                <div class="imagen-paso-container">
                    <img src="../img/landingpage.png" alt="Vista General" class="img-paso-guia">
                </div>
                <h3>EXPLORA LA PAGINA</h3>
                <p>Ingresá al complejo digital, revisá la ubicación exacta en el mapa, nuestros horarios y todas las instalaciones que tenemos preparadas para vos.</p>
            </div>

            <!-- PASO 02 -->
            <div class="tarjeta-paso-guia">
                <div class="imagen-paso-container">
                    <img src="../img/crear-cuenta.png" alt="Crear Cuenta" class="img-paso-guia">
                </div>
                <h3>CREA TU CUENTA</h3>
                <p>Si sos nuevo, registrate en pocos segundos con tus datos personales para habilitar tu perfil de cliente y empezar a gestionar tus reservas.</p>
            </div>

            <!-- PASO 03 -->
            <div class="tarjeta-paso-guia">
                <div class="imagen-paso-container">
                    <img src="../img/iniciar-sesion.png" alt="Iniciar Sesión" class="img-paso-guia">
                </div>
                <h3>INICIA SESION Y REALIZA LA RESERVA</h3>
                <p>Logueate con tu correo y contraseña, seleccioná tu deporte, elegí la cancha, la fecha y el horario que prefieras. ¡Confirmá y a jugar!</p>
            </div>

            <!-- PASO 04 -->
            <div class="tarjeta-paso-guia">
                <div class="imagen-paso-container">
                    <img src="../img/recuperar-contrasenia.png" alt="Recuperar Contraseña" class="img-paso-guia">
                </div>
                <h3>RECUPERA TU CONTRASEÑA</h3>
                <p>¿Olvidaste tu contraseña? No te preocupes. Usá la opción de recuperación, ingresá tu correo y te enviaremos las instrucciones para restablecerla al instante.</p>
            </div>

        </div>

        <div class="centrar-boton-final">
            <a href="login_cliente.php" class="btn-reservar-final">
                <i class="fas fa-calendar-check"></i> ¡IR A RESERVAR MI CANCHA!
            </a>
        </div>
    </section>

    <!-- FOOTER MODERNO EXACTO AL LANDING PAGE -->
    <footer class="footer-landing">
        <div class="footer-contenido">
            <div class="footer-col">
                <h4>PLANETA FUTBOL</h4>
                <p>El mejor complejo deportivo de Tucumán para disfrutar del fútbol y pádel con amigos. ¡Te esperamos para el tercer tiempo!</p>
            </div>
            
            <div class="footer-col">
                <h4>ENLACES RÁPIDOS</h4>
                <ul>
                    <li><a href="como_funciona.php">¿Cómo Funciona?</a></li>
                    <li><a href="login_cliente.php">Iniciar Sesión</a></li>
                    <li><a href="registro_cliente.php">Crear Cuenta</a></li>
                    <li><a href="index.php#ubicacion-horarios">Ubicación y Horarios</a></li>
                </ul>
            </div>
            
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
