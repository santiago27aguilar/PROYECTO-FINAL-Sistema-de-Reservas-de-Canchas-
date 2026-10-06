<?php
session_start();
if (!isset($_SESSION['id_cliente'])) {
    header("Location: login_cliente.php");
    exit();
}

include '../php/conexion.php';

$nombre_cliente = $_SESSION['cliente_nombre'];

// --- LÓGICA DEL CALENDARIO DE DISPONIBILIDAD PARA EL CLIENTE ---
$fecha_seleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$filtro_categoria = isset($_GET['categoria']) ? $_GET['categoria'] : 'Todas';

// Consulta de canchas según la categoría seleccionada
$query_canchas = "SELECT idcancha, tipo_cancha, precio_hora FROM cancha ";
if ($filtro_categoria !== 'Todas') {
    $query_canchas .= "WHERE tipo_cancha LIKE :categoria ";
}
$canchas_stmt = $conexion->prepare($query_canchas);
if ($filtro_categoria !== 'Todas') {
    $canchas_stmt->execute([':categoria' => "%$filtro_categoria%"]);
} else {
    $canchas_stmt->execute();
}
$canchas = $canchas_stmt->fetchAll(PDO::FETCH_ASSOC);

// Consultar reservas del día (SOLO ocupado, sin revelar nombres por privacidad)
$sql_reservas = "SELECT r.cancha_idcancha, r.hora_inicio, r.hora_fin FROM reservas r WHERE DATE(r.hora_inicio) = :fecha";
$reservas_stmt = $conexion->prepare($sql_reservas);
$reservas_stmt->execute([':fecha' => $fecha_seleccionada]);
$reservas_dia = $reservas_stmt->fetchAll(PDO::FETCH_ASSOC);

$matriz_reservas = [];
foreach ($reservas_dia as $reserva) {
    $hora_inicio = (int)date('H', strtotime($reserva['hora_inicio']));
    $hora_fin = (int)date('H', strtotime($reserva['hora_fin']));
    $duracion = $hora_fin - $hora_inicio; 
    if ($duracion < 0) $duracion += 24; 
    
    $matriz_reservas[$reserva['cancha_idcancha']][$hora_inicio] = 'ocupado';
    
    if ($duracion == 2) {
        $hora_sig = ($hora_inicio + 1) % 24;
        $matriz_reservas[$reserva['cancha_idcancha']][$hora_sig] = 'bloque_continuacion';
    }
}

$horario_apertura = 14; 
$horario_cierre = 24; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Cancha - Planeta de Futbol</title>
    <?php include 'head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_cliente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
 
<!-- CONTENEDOR GENERAL PARA CENTRAR TODO -->
<div class="contenedor-principal">

    <!-- ========================================== -->
    <!-- TARJETA 1: CALENDARIO DE DISPONIBILIDAD    -->
    <!-- ========================================== -->
    <div class="reserva-card">

        <!-- MENSAJES DE ÉXITO O ERROR (Mantenidos arriba de todo) -->
        <?php if (isset($_GET['reserva']) && $_GET['reserva'] == 'ok'): ?>
            <?php 
                $num = "5493814152422"; 
                $texto = rawurlencode("¡Hola! Soy $nombre_cliente. Acabo de solicitar un turno en la web. Te adjunto el comprobante de pago para confirmarlo.");
            ?>
            <div class="mensaje-exito">
                <h3>¡Pre-Reserva realizada con Éxito!</h3>
                <p class="estado-pago">ESTADO: PAGO PENDIENTE</p>
                <div>
                    <a href="https://api.whatsapp.com/send?phone=<?php echo $num; ?>&text=<?php echo $texto; ?>" target="_blank" class="btn-whatsapp">Contactar por WhatsApp</a>
                </div>
                
                <p class="info-sena">
                    Para <strong>confirmar tu turno definitivamente</strong>, transfiere el monto de la seña (50%) enviando el comprobante.<br>
                    <span class="texto-saldo">El monto restante lo podés abonar en efectivo o continuar pagando con transferencia en el complejo.</span>
                </p>
                
                <p>Si realizas el pago por transferencia (Alias: <span class="alias-destacado">planeta.futbol.padel</span>)</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="mensaje-error">
                <?php if ($_GET['error'] == 'ocupado'): ?>
                    <h3>¡Horario Ocupado!</h3>
                    <p>El horario ya se encuentra reservado. Por favor, elige otra hora u otra cancha.</p>
                <?php elseif ($_GET['error'] == 'fecha_pasada'): ?>
                    <h3>¡Fecha Inválida! o ¡Fuera de Horario!</h3>
                    <p>No podés reservar un turno en una fecha u hora que ya pasó. Elige una fecha futura o cambia de horario.</p>
                <?php elseif ($_GET['error'] == 'fuera_horario'): ?>
                    <h3>¡Fuera de Horario!</h3>
                    <p>El horario seleccionado está fuera de nuestro rango de atención (14:00 a 00:00 hs).</p>
                <?php elseif ($_GET['error'] == 'duracion_invalida'): ?>
                    <h3>¡Duración Incorrecta!</h3>
                    <p>Los turnos solo pueden ser en bloques de 1 hora o 2 horas. Por favor, ajustá la duración.</p>
                <?php else: ?>
                    <h3>¡Error Inesperado!</h3>
                    <p>Ocurrió un problema al intentar procesar tu reserva. Volvé a intentarlo.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- GRILLA DEL CALENDARIO -->
        <div class="seccion-disponibilidad">
            <h3 class="titulo-disponibilidad">ESTADO DE LAS CANCHAS</h3>
            <p class="subtitulo-disponibilidad">Consultá la disponibilidad horaria antes de reservar</p>

            <!-- CONTROLES (FECHA Y PESTAÑAS) - CLASE CORREGIDA A .controles-calendario -->
            <form method="GET" class="controles-calendario">
                <div class="control-fecha-cliente">
                    <label>Fecha:</label>
                    <input type="date" name="fecha" value="<?php echo htmlspecialchars($fecha_seleccionada); ?>" min="<?php echo date('Y-m-d'); ?>" onchange="this.form.submit()">
                </div>

                <div class="tabs-canchas">
                    <input type="hidden" name="categoria" id="input_categoria" value="<?php echo htmlspecialchars($filtro_categoria); ?>">
                    <button type="button" class="tab-btn <?php echo ($filtro_categoria == 'Futbol 5') ? 'activo' : ''; ?>" onclick="cambiarCategoria('Futbol 5')">Fútbol 5</button>
                    <button type="button" class="tab-btn <?php echo ($filtro_categoria == 'Futbol 7') ? 'activo' : ''; ?>" onclick="cambiarCategoria('Futbol 7')">Fútbol 7</button>
                    <button type="button" class="tab-btn <?php echo ($filtro_categoria == 'Padel') ? 'activo' : ''; ?>" onclick="cambiarCategoria('Padel')">Pádel</button>
                    <button type="button" class="tab-btn <?php echo ($filtro_categoria == 'Todas') ? 'activo' : ''; ?>" onclick="cambiarCategoria('Todas')">Ver Todas</button>
                </div>
            </form>

            <!-- GRILLA DE TURNOS -->
            <div class="table-responsive-wrapper">
                <table class="tabla-moderna tabla-calendario">
                    <thead>
                        <tr>
                            <th class="celda-header"><i class="far fa-clock"></i> Hora</th>
                            <?php foreach ($canchas as $cancha): ?>
                                <th class="celda-header"><?php echo htmlspecialchars($cancha['tipo_cancha']); ?></th>
                            <?php endforeach; ?>
                            <?php if(empty($canchas)): ?>
                                <th class="celda-header">No hay canchas registradas</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($hora = $horario_apertura; $hora <= $horario_cierre; $hora++): ?>
                            <?php 
                                $hora_real = ($hora == 24) ? 0 : $hora;
                                $texto_hora = str_pad($hora_real, 2, '0', STR_PAD_LEFT); 
                            ?>
                            <tr>
                                <td class="celda-hora"><strong><?php echo $texto_hora; ?>:00</strong></td>

                                <?php foreach ($canchas as $cancha): ?>
                                    <?php 
                                        $idcancha = $cancha['idcancha'];
                                        
                                        if (isset($matriz_reservas[$idcancha][$hora_real])) {
                                            // OCUPADO
                                            echo "<td class='celda-ocupado'>";
                                            echo "<strong>Ocupado</strong>";
                                            echo "</td>";
                                        } else {
                                            // LIBRE
                                            echo "<td class='celda-vacia'>";
                                            echo "<span class='celda-libre-texto'><i class='fas fa-check-circle'></i> Libre</span>";
                                            echo "</td>";
                                        }
                                    ?>
                                <?php endforeach; ?>
                                
                                <?php if(empty($canchas)): ?>
                                    <td class="celda-vacia celda-sin-canchas"></td>
                                <?php endif; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div> <!-- FIN DE LA TARJETA 1 -->


    <!-- ========================================== -->
    <!-- TARJETA 2: FORMULARIO DE RESERVA           -->
    <!-- ========================================== -->
    <div class="reserva-card">
        <!-- TÍTULO Y LOGO ALINEADOS -->
        <div class="header-titulo">
            <img src="../img/icono-turno.png" alt="Icono" class="icono-usuario-inline">
            <h2>RESERVA TU TURNO</h2>
        </div>

        <!-- FORMULARIO DE RESERVA -->
        <form action="../php/procesar_reserva.php" method="POST">
            
            <div class="form-main-grid"> 
                
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
                        <input type="date" name="fecha_reserva" id="fecha_reserva" min="<?php echo date('Y-m-d'); ?>" required>
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
    </div> <!-- FIN DE LA TARJETA 2 -->

</div> <!-- FIN DEL CONTENEDOR PRINCIPAL -->

<!-- SCRIPTS LINKADOS EXTERNAMENTE -->
<script src="../js/logica_reserva.js"></script>
<script src="../js/tabs_calendario.js"></script>

</body>
</html>
