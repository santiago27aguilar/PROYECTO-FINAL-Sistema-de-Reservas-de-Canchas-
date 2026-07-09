<?php
session_start();
if (!isset($_SESSION['id_cliente'])) {
    header("Location: login_cliente.php");
    exit();
}

include '../php/conexion.php';

$nombre_cliente = $_SESSION['cliente_nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Cancha - Pampa Fútbol</title>
    <link rel="stylesheet" href="../css/estilos_cliente.css">
</head>
<body>
 
<div class="reserva-card">

    <?php if (isset($_GET['reserva']) && $_GET['reserva'] == 'ok'): ?>
        <?php 
            $num = "5493814152422"; 
            $texto = rawurlencode("¡Hola! Soy $nombre_cliente. Solicité un turno en Pampa Fútbol y quiero confirmar el pago.");
        ?>
        <div class="mensaje-exito">
            <h3>¡Turno Reservado con Éxito!</h3>
            <p class="estado-pago">ESTADO: PAGO PENDIENTE</p>
            <div>
                <a href="https://api.whatsapp.com/send?phone=<?php echo $num; ?>&text=<?php echo $texto; ?>" target="_blank" class="btn-whatsapp">Contactar por WhatsApp</a>
            </div>
            <p>Si pagás por transferencia, Alias: <span class="alias-destacado">planeta.futbol.padel</span></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="mensaje-error">
            <?php if ($_GET['error'] == 'ocupado'): ?>
                <h3>¡Horario Ocupado!</h3>
                <p>El horario ya se encuentra reservado. Por favor, elige otra hora u otra cancha.</p>
            <?php elseif ($_GET['error'] == 'fecha_pasada'): ?>
                <h3>¡Fecha Inválida!</h3>
                <p>No podés reservar un turno en una fecha que ya pasó. Elegí el día de hoy o una fecha futura.</p>
            <?php elseif ($_GET['error'] == 'fuera_horario'): ?>
                <h3>¡Fuera de Horario!</h3>
                <p>El horario seleccionado está fuera de nuestro rango de atención (15:00 a 00:00 hs).</p>
            <?php elseif ($_GET['error'] == 'duracion_invalida'): ?>
                <h3>¡Duración Incorrecta!</h3>
                <p>Los turnos solo pueden ser en bloques de 1 hora o 2 horas. Por favor, ajustá la duración.</p>
            <?php else: ?>
                <h3>¡Error Inesperado!</h3>
                <p>Ocurrió un problema al intentar procesar tu reserva. Volvé a intentarlo.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    

    <h2>RESERVA TU TURNO</h2>
    <img src="../img/icono-turno.png" alt="Icono" class="icono-usuario">

    <form action="../php/procesar_reserva.php" method="POST">
        
        <div class="form-main-grid" style="display: block;"> 
            
            <div class="form-row-2">
                <div class="form-group">
                    <label>CANCHA<span class="asterisco">*</span></label>
                    <select name="idcancha" id="id_cancha" required>
                        <option value="">Seleccionar...</option>
                        <?php
                            $q = $conexion->query("SELECT idcancha, tipo_cancha, precio_hora FROM cancha");
                            while($r = $q->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='".$r['idcancha']."' data-precio='".$r['precio_hora']."'>".$r['tipo_cancha']."</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>FECHA<span class="asterisco">*</span></label>
                    <input type="date" name="fecha_reserva" id="fecha_reserva" required>
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label>DURACION<span class="asterisco">*</span></label>
                    <select name="duracion" id="duracion_turno" required>
                        <option value="1">1 Hora</option>
                        <option value="2">2 Horas</option>
                    </select>
                </div>
                <div class="form-group"> 
                    <label>HORA de INICIO<span class="asterisco">*</span></label>
                    <select name="hora_inicio" id="hora_reserva" required>
                        <option value="">Elegir Horario...</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <div id="cuadro_precio" class="precio-banner invisible">
                    <p>
                        <span id="texto_duracion">Total:</span> 
                        <strong id="precio_final">$0</strong>
                    </p>
                </div>
            </div>

        </div>

        <div class="footer-formulario">
            <div class="gestion-container">
                <a href="../html/mis_reservas.php" class="link-gestion">VER MIS RESERVAS</a>
            </div>
            <button type="submit" class="btn-enviar">CONFIRMAR TURNO</button>
        </div>
        
    </form>
</div>

<script src="../js/logica_reserva.js"></script>

</body>
</html>
