<?php
$reservas = []; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'conexion.php'; 
    $dni = trim($_POST['dni']);
    $correo = trim($_POST['correo']);

    try {
        // Traemos TODAS las reservas futuras (hora_inicio >= NOW())
        $sql = "SELECT r.idreservas, r.hora_inicio, r.hora_fin, c.tipo_cancha 
                FROM reservas r
                JOIN clientes cli ON r.clientes_idclientes = cli.idclientes
                JOIN cancha c ON r.cancha_idcancha = c.idcancha
                WHERE cli.dni = :dni AND cli.correo = :correo
                AND r.hora_inicio >= NOW() 
                ORDER BY r.hora_inicio ASC";
                
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':dni', $dni);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        // Usamos fetchAll para traer un array con múltiples resultados
        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tus Turnos - Planeta de Futbol</title>
    <?php include '../html/head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_buscar_reserva.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="reserva-card">
    
    <?php if (count($reservas) > 0): ?>
        
        <!-- ============================================== -->
        <!-- VISTA 1: LISTADO DE TURNOS CON FILTROS         -->
        <!-- ============================================== -->
        <div id="vista-lista">
            <h2>TUS TURNOS ACTIVOS</h2>
            
            <!-- Botones de Filtro -->
            <div class="tabs-canchas">
                <button type="button" class="tab-btn activo" onclick="filtrarTurnos('Todas', this)">Ver Todas</button>
                <button type="button" class="tab-btn" onclick="filtrarTurnos('Futbol 5', this)">Fútbol 5</button>
                <button type="button" class="tab-btn" onclick="filtrarTurnos('Futbol 7', this)">Fútbol 7</button>
                <button type="button" class="tab-btn" onclick="filtrarTurnos('Padel', this)">Pádel</button>
            </div>

            <!-- Contenedor de las mini tarjetas -->
            <div class="contenedor-lista-turnos">
                <?php foreach ($reservas as $index => $reserva): ?>
                    <div class="mini-tarjeta-turno" data-categoria="<?php echo htmlspecialchars($reserva['tipo_cancha']); ?>">
                        <div class="mini-info">
                            <strong><?php echo htmlspecialchars($reserva['tipo_cancha']); ?></strong>
                            <span><?php echo date('d/m/Y - H:i', strtotime($reserva['hora_inicio'])); ?>hs</span>
                        </div>
                        <button class="btn-ver-detalle" onclick="mostrarDetalle(<?php echo $index; ?>)">Ver Detalle</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="footer-acciones">
                <a href="../html/mis_reservas.php" class="link-volver">VOLVER AL BUSCADOR</a>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- VISTA 2: DETALLES DE CADA TURNO (OCULTOS)      -->
        <!-- ============================================== -->
        <?php foreach ($reservas as $index => $reserva): ?>
            <div id="detalle-<?php echo $index; ?>" class="vista-detalle oculto">
                <h2>DETALLE DE TU TURNO</h2>
                
                <div class="contenedor-detalle">
                    <div class="tarjeta-fila">
                        <div class="cuadro-img">
                            <img src="../img/canchaf5f7padel.png" alt="Cancha">
                        </div>
                        <div class="info-texto">
                            <span class="label">CANCHA:</span>
                            <span class="dato"><?php echo htmlspecialchars($reserva['tipo_cancha']); ?></span>
                        </div>
                    </div>

                    <div class="tarjeta-fila">
                        <div class="cuadro-img">
                            <img src="../img/logo-reserva.png" alt="Fecha">
                        </div>
                        <div class="info-texto">
                            <span class="label">FECHA:</span>
                            <span class="dato"><?php echo date('d/m/Y', strtotime($reserva['hora_inicio'])); ?></span>
                        </div>
                    </div> 

                    <div class="tarjeta-fila">
                        <div class="cuadro-img">
                            <img src="../img/reloj-hora.png" alt="Horario">
                        </div>
                        <div class="info-texto">
                            <span class="label">HORARIO:</span>
                            <span class="dato"><?php echo date('H:i', strtotime($reserva['hora_inicio'])) . ' a ' . date('H:i', strtotime($reserva['hora_fin'])); ?>hs</span>
                        </div>
                    </div>
                </div>

                <div class="footer-acciones">
                    <form action="cancelar_turno.php" method="POST" class="form-cancelar" onsubmit="return confirm('¿Seguro quieres cancelar tu turno?');">
                        <input type="hidden" name="id_reserva" value="<?php echo $reserva['idreservas']; ?>">
                        <button type="submit" class="btn-cancelar">CANCELAR MI TURNO</button>
                    </form>
                    
                    <button type="button" class="link-volver btn-como-link" onclick="volverALista(<?php echo $index; ?>)">VOLVER Al BUSCADOR</button>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        
        <!-- ============================================== -->
        <!-- VISTA DE ERROR: NO SE ENCONTRÓ NADA            -->
        <!-- ============================================== -->
        <h2>DETALLE DE TU TURNO</h2>
        <div class="contenedor-detalle">
            <div class="tarjeta-fila">
                <div class="cuadro-img-error">
                    <img src="../img/error.png" alt="Error">
                </div>
                <div class="info-texto-error">
                    <span class="label">ESTADO:</span>
                    <span class="dato-error">No encontramos tu RESERVA</span>
                </div>
            </div>
        </div>

        <div class="footer-acciones">
            <p class="error-msg-sub">Por favor, revisá que el DNI y el Correo Electrónico coincidan con los que ingresaste o que tu turno no haya pasado ya.</p>
            <a href="../html/mis_reservas.php" class="link-volver">VOLVER A BUSCAR</a>
        </div>

    <?php endif; ?>
</div>

<script src="../js/gestion_reservas.js"></script>
</body>
</html>
