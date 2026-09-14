<?php
    session_start();
    
    if(!isset($_SESSION['usuario_nombre'])){
        header("Location: login.php");
        exit();
    }

    $rol = strtolower($_SESSION['usuario_rol']);

    if (!in_array($rol, ['duenio', 'dueño'])) {
        header("Location: reservas.php?error=sin_permisos");
        exit();
    }

    include '../php/conexion.php';

    $fecha_seleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

    $canchas_stmt = $conexion->query("SELECT idcancha, tipo_cancha FROM cancha");
    $canchas = $canchas_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $sql_reservas = "SELECT r.*, c.nombre, c.apellido, c.telefono FROM reservas r LEFT JOIN clientes c ON r.clientes_idclientes = c.idclientes WHERE DATE(r.hora_inicio) = :fecha";
    
    $reservas_stmt = $conexion->prepare($sql_reservas);
    $reservas_stmt->execute([':fecha' => $fecha_seleccionada]);
    $reservas_dia = $reservas_stmt->fetchAll(PDO::FETCH_ASSOC);

    $matriz_reservas = [];
    foreach ($reservas_dia as $reserva) {
        $hora_inicio = (int)date('H', strtotime($reserva['hora_inicio']));
        $hora_fin = (int)date('H', strtotime($reserva['hora_fin']));
        $duracion = $hora_fin - $hora_inicio; 
        
        // Corrección por si el turno pasa la medianoche
        if ($duracion < 0) $duracion += 24; 
        
        $matriz_reservas[$reserva['cancha_idcancha']][$hora_inicio] = $reserva;
        
        if ($duracion == 2) {
            $hora_sig = ($hora_inicio + 1) % 24;
            $matriz_reservas[$reserva['cancha_idcancha']][$hora_sig] = 'bloque_continuacion';
        }
    }

    // --- RANGO DE HORARIOS AJUSTADO ---
    $horario_apertura = 14; 
    $horario_cierre = 24; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Reservas - Pampa Fútbol</title>
    <link rel="stylesheet" href="../css/estilos_calendario.css?v=<?php echo time() + 1; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    
    <div class="navbar">
        <div class="menu-toggle" id="mobile-menu"><i class="fas fa-bars"></i></div>
        <div class="nav-links" id="nav-links">
            
            <?php if(isset($_SESSION['usuario_rol']) && in_array(strtolower($_SESSION['usuario_rol']), ['duenio', 'dueño'])): ?>
                <a href="dashboard.php">Tablero</a>
                <a href="personal.php">Personal</a>
                <a href="calendario.php" class="link-activo">Calendario</a> 
            <?php endif; ?>
            
            <a href="inicio.php">Clientes</a>
            <a href="reservas.php">Reservas</a>
            <a href="canchas.php">Canchas</a>
            <a href="pagos.php">Pagos</a>
            
            <?php if(isset($_SESSION['usuario_nombre']) && isset($_SESSION['usuario_rol'])): ?>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i> 
                    <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong> 
                    <span class="user-rol">(<?= htmlspecialchars($_SESSION['usuario_rol']) ?>)</span>
                </div>
            <?php endif; ?>
            <a href="../php/cerrar_sesion.php" class="btn-salir">Cerrar Sesion</a>
        </div>
    </div>

    <div class="container">
        <div class="contenedor-calendario">
            <h2 class="titulo-calendario">CALENDARIO DE TURNOS</h2>
            
            <form method="GET" class="control-fecha">
                <label><strong>Seleccionar Día:</strong></label>
                <input type="date" name="fecha" value="<?php echo $fecha_seleccionada; ?>" onchange="this.form.submit()">
            </form>

            <div class="table-responsive-wrapper">
                <table class="tabla-moderna tabla-calendario">
                    <thead>
                        <tr>
                            <th class="celda-header"><i class="far fa-clock"></i> Hora</th>
                            <?php foreach ($canchas as $cancha): ?>
                                <th class="celda-header"><?php echo htmlspecialchars($cancha['tipo_cancha']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($hora = $horario_apertura; $hora <= $horario_cierre; $hora++): ?>
                            <?php 
                                // Convertimos el 24 en 0 para la base de datos y la vista
                                $hora_real = ($hora == 24) ? 0 : $hora;
                                $texto_hora = str_pad($hora_real, 2, '0', STR_PAD_LEFT); 
                            ?>
                            <tr>
                                <td class="celda-hora"><strong><?php echo $texto_hora; ?>:00</strong></td>

                                <?php foreach ($canchas as $cancha): ?>
                                    <?php 
                                        $idcancha = $cancha['idcancha'];
                                        
                                        if (isset($matriz_reservas[$idcancha][$hora_real])) {
                                            $reserva = $matriz_reservas[$idcancha][$hora_real];
                                            
                                            if ($reserva === 'bloque_continuacion') {
                                                echo "<td class='celda-ocupado celda-continuacion'><small>(Continuación)</small></td>";
                                            } else {
                                                $nombre_cliente = htmlspecialchars($reserva['nombre'] . " " . $reserva['apellido']);
                                                $telefono = htmlspecialchars($reserva['telefono'] != '' ? $reserva['telefono'] : 'No registrado');
                                                
                                                echo "<td class='celda-ocupado' style='cursor: pointer;' onclick=\"alert('DATOS DEL TURNO\\n\\nJugador: {$nombre_cliente}\\nTeléfono: {$telefono}')\" title='Clic para ver datos'>";
                                                echo "<strong>{$nombre_cliente}</strong><br>";
                                                echo "<small>Confirmado</small>";
                                                echo "</td>";
                                            }
                                        } else {
                                            $hora_exacta = $texto_hora . ':00';
                                            $enlace = "reservas.php?cancha_pre={$idcancha}&fecha_pre={$fecha_seleccionada}&hora_pre={$hora_exacta}";
                                            
                                            echo "<td class='celda-vacia'>";
                                            echo "<a href='{$enlace}' class='celda-libre' title='Hacer reserva'>";
                                            echo "<i class='fas fa-plus-circle'></i> Libre";
                                            echo "</a>";
                                            echo "</td>";
                                        }
                                    ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script src="../js/menu_desplegable.js"></script>
</body>
</html>
