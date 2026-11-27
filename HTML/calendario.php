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

    $fecha_hoy = date('Y-m-d');
    $fecha_raw = isset($_GET['fecha']) ? trim($_GET['fecha']) : $fecha_hoy;
    $timestamp_fecha = strtotime($fecha_raw);
    $fecha_seleccionada = ($timestamp_fecha !== false) ? date('Y-m-d', $timestamp_fecha) : $fecha_hoy;
    $ts_actual = strtotime($fecha_seleccionada);

    $fecha_anterior = date('Y-m-d', strtotime('-1 day', $ts_actual));
    $fecha_siguiente = date('Y-m-d', strtotime('+1 day', $ts_actual));

    // Mes de referencia para el mini-calendario (puede navegarse independiente o seguir al día elegido)
    $mes_ref_raw = isset($_GET['mes']) ? trim($_GET['mes']) : date('Y-m', $ts_actual);
    $ts_mes_ref = strtotime($mes_ref_raw . '-01');
    if ($ts_mes_ref === false) {
        $ts_mes_ref = strtotime(date('Y-m-01', $ts_actual));
    }
    $anio_mes = (int)date('Y', $ts_mes_ref);
    $mes_num = (int)date('n', $ts_mes_ref);
    $mes_param = sprintf('%04d-%02d', $anio_mes, $mes_num);
    $mes_anterior_param = date('Y-m', strtotime('-1 month', $ts_mes_ref));
    $mes_siguiente_param = date('Y-m', strtotime('+1 month', $ts_mes_ref));
    $mes_actual_param = date('Y-m');

    $dias_es = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    $meses_es = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    $fecha_legible = $dias_es[(int)date('w', $ts_actual)] . ', ' . (int)date('j', $ts_actual) . ' de ' . $meses_es[(int)date('n', $ts_actual)] . ' ' . date('Y', $ts_actual);
    $titulo_mes_mini = $meses_es[$mes_num] . ' ' . $anio_mes;

    $filtro_categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : 'Futbol 5';
    $categorias_validas = ['Futbol 5', 'Futbol 7', 'Padel', 'Todas'];
    if (!in_array($filtro_categoria, $categorias_validas, true)) {
        $filtro_categoria = 'Futbol 5';
    }

    $query_canchas = "SELECT idcancha, tipo_cancha FROM cancha ";
    if ($filtro_categoria !== 'Todas') {
        $query_canchas .= "WHERE tipo_cancha LIKE :categoria ";
    }
    $query_canchas .= "ORDER BY idcancha ASC";
    
    $canchas_stmt = $conexion->prepare($query_canchas);
    
    if ($filtro_categoria !== 'Todas') {
        $canchas_stmt->execute([':categoria' => '%' . $filtro_categoria . '%']);
    } else {
        $canchas_stmt->execute();
    }
    
    $canchas = $canchas_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Marcadores mensuales para el mini-calendario (por día del mes)
    $primer_dia_mes = sprintf('%04d-%02d-01', $anio_mes, $mes_num);
    $ultimo_dia_mes = date('Y-m-t', $ts_mes_ref);

    $sql_mes = "SELECT DATE(r.hora_inicio) AS dia, LOWER(COALESCE(r.estado, 'confirmado')) AS estado_low
                FROM reservas r
                JOIN cancha ca ON r.cancha_idcancha = ca.idcancha
                WHERE DATE(r.hora_inicio) BETWEEN :d_ini AND :d_fin
                  AND LOWER(COALESCE(r.estado, '')) <> 'cancelado'";
    $params_mes = [
        ':d_ini' => $primer_dia_mes,
        ':d_fin' => $ultimo_dia_mes
    ];
    if ($filtro_categoria !== 'Todas') {
        $sql_mes .= " AND ca.tipo_cancha LIKE :cat_mes";
        $params_mes[':cat_mes'] = '%' . $filtro_categoria . '%';
    }
    $stmt_mes = $conexion->prepare($sql_mes);
    $stmt_mes->execute($params_mes);

    $puntos_por_dia = [];
    while ($m = $stmt_mes->fetch(PDO::FETCH_ASSOC)) {
        $dia_str = $m['dia'];
        $est = $m['estado_low'];
        if (!isset($puntos_por_dia[$dia_str])) {
            $puntos_por_dia[$dia_str] = [];
        }
        $tipo_punto = 'confirmado';
        if ($est === 'pendiente') {
            $tipo_punto = 'pendiente';
        } elseif ($est === 'pagado') {
            $tipo_punto = 'pagado';
        }
        if (!in_array($tipo_punto, $puntos_por_dia[$dia_str], true)) {
            $puntos_por_dia[$dia_str][] = $tipo_punto;
        }
    }

    // Reservas del día seleccionado
    $sql_reservas = "SELECT r.*, c.nombre, c.apellido, c.telefono 
                     FROM reservas r 
                     LEFT JOIN clientes c ON r.clientes_idclientes = c.idclientes 
                     WHERE DATE(r.hora_inicio) = :fecha";
    
    $reservas_stmt = $conexion->prepare($sql_reservas);
    $reservas_stmt->execute([':fecha' => $fecha_seleccionada]);
    $reservas_dia = $reservas_stmt->fetchAll(PDO::FETCH_ASSOC);

    $matriz_reservas = [];
    foreach ($reservas_dia as $reserva) {
        if (strtolower((string)($reserva['estado'] ?? '')) === 'cancelado') {
            continue;
        }

        $hora_inicio = (int)date('H', strtotime($reserva['hora_inicio']));
        $hora_fin = (int)date('H', strtotime($reserva['hora_fin']));
        $duracion = $hora_fin - $hora_inicio;
        
        if ($duracion <= 0) {
            $duracion += 24;
        }
        if ($duracion > 6) {
            $duracion = 1;
        }
        
        $matriz_reservas[(int)$reserva['cancha_idcancha']][$hora_inicio] = $reserva;
        
        for ($paso = 1; $paso < $duracion; $paso++) {
            $hora_sig = ($hora_inicio + $paso) % 24;
            if (!isset($matriz_reservas[(int)$reserva['cancha_idcancha']][$hora_sig])) {
                $matriz_reservas[(int)$reserva['cancha_idcancha']][$hora_sig] = [
                    'continuacion' => true,
                    'reserva_origen' => $reserva
                ];
            }
        }
    }

    $horario_apertura = 14; 
    $horario_cierre = 24; 

    $horas_dia = [];
    $horas_tarde = [];
    $horas_noche = [];

    for ($h = $horario_apertura; $h <= $horario_cierre; $h++) {
        $h_real = ($h == 24) ? 0 : $h;
        $item = [
            'real' => $h_real,
            'texto' => str_pad($h_real, 2, '0', STR_PAD_LEFT) . ':00'
        ];
        $horas_dia[] = $item;

        if ($h >= 14 && $h <= 18) {
            $horas_tarde[] = $item;
        } else {
            $horas_noche[] = $item;
        }
    }

    $total_bloques = count($canchas) * count($horas_dia);
    $bloques_ocupados = 0;
    foreach ($canchas as $c) {
        $cid = (int)$c['idcancha'];
        foreach ($horas_dia as $hd) {
            if (isset($matriz_reservas[$cid][$hd['real']])) {
                $bloques_ocupados++;
            }
        }
    }
    $bloques_libres = max(0, $total_bloques - $bloques_ocupados);

    // Cálculo de celdas de la cuadrícula mensual (Lunes = 1 a Domingo = 7)
    $primer_dia_semana = (int)date('N', $ts_mes_ref);
    $dias_en_mes = (int)date('t', $ts_mes_ref);
    $ts_mes_ant = strtotime('-1 month', $ts_mes_ref);
    $dias_mes_ant = (int)date('t', $ts_mes_ant);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Turnos - Planeta Fútbol</title>
    <?php include 'head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_calendario.css?v=4">
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
        <div class="card-blanca">
            <h2 class="titulo-calendario">CALENDARIO DE TURNOS</h2>

            <div class="subtitulo-dia">
                <span class="fecha-activa-texto"><?= htmlspecialchars($fecha_legible) ?></span>
                <?php if (!empty($canchas)): ?>
                    <span class="resumen-ocupacion">
                        <strong><?= $bloques_ocupados ?></strong> ocupados · <strong><?= $bloques_libres ?></strong> libres
                    </span>
                <?php endif; ?>
            </div>
            
            <form method="GET" action="calendario.php" id="formCalendario" class="controles-calendario">
                <input type="hidden" name="mes" value="<?= htmlspecialchars($mes_param) ?>">

                <div class="control-fecha-grupo">
                    <a href="calendario.php?fecha=<?= urlencode($fecha_anterior) ?>&categoria=<?= urlencode($filtro_categoria) ?>&mes=<?= urlencode(date('Y-m', strtotime($fecha_anterior))) ?>" class="btn-nav-dia" title="Día anterior">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>

                    <div class="control-fecha">
                        <label for="input_fecha"><strong>Día:</strong></label>
                        <input type="date" id="input_fecha" name="fecha" value="<?= htmlspecialchars($fecha_seleccionada) ?>" onchange="this.form.submit()">
                    </div>

                    <?php if ($fecha_seleccionada !== $fecha_hoy): ?>
                        <a href="calendario.php?fecha=<?= urlencode($fecha_hoy) ?>&categoria=<?= urlencode($filtro_categoria) ?>&mes=<?= urlencode($mes_actual_param) ?>" class="btn-hoy-dia">
                            Hoy
                        </a>
                    <?php endif; ?>

                    <a href="calendario.php?fecha=<?= urlencode($fecha_siguiente) ?>&categoria=<?= urlencode($filtro_categoria) ?>&mes=<?= urlencode(date('Y-m', strtotime($fecha_siguiente))) ?>" class="btn-nav-dia" title="Día siguiente">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                </div>

                <div class="tabs-canchas">
                    <input type="hidden" name="categoria" id="input_categoria" value="<?= htmlspecialchars($filtro_categoria) ?>">
                    
                    <button type="button" class="tab-btn <?= ($filtro_categoria == 'Futbol 5') ? 'activo' : '' ?>" onclick="cambiarCategoria('Futbol 5')">Fútbol 5</button>
                    <button type="button" class="tab-btn <?= ($filtro_categoria == 'Futbol 7') ? 'activo' : '' ?>" onclick="cambiarCategoria('Futbol 7')">Fútbol 7</button>
                    <button type="button" class="tab-btn <?= ($filtro_categoria == 'Padel') ? 'activo' : '' ?>" onclick="cambiarCategoria('Padel')">Pádel</button>
                    <button type="button" class="tab-btn <?= ($filtro_categoria == 'Todas') ? 'activo' : '' ?>" onclick="cambiarCategoria('Todas')">Ver Todas</button>
                </div>

            </form>

            <!-- MINI-CALENDARIO MENSUAL CON PUNTITOS DE ESTADO -->
            <div class="mini-calendario-box">
                <div class="mini-cal-header">
                    <a href="calendario.php?fecha=<?= urlencode($fecha_seleccionada) ?>&categoria=<?= urlencode($filtro_categoria) ?>&mes=<?= urlencode($mes_actual_param) ?>" class="mini-cal-hoy">
                        Hoy
                    </a>
                    <div class="mini-cal-nav">
                        <a href="calendario.php?fecha=<?= urlencode($fecha_seleccionada) ?>&categoria=<?= urlencode($filtro_categoria) ?>&mes=<?= urlencode($mes_anterior_param) ?>" class="mini-cal-flecha" title="Mes anterior">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <span class="mini-cal-titulo"><?= htmlspecialchars($titulo_mes_mini) ?></span>
                        <a href="calendario.php?fecha=<?= urlencode($fecha_seleccionada) ?>&categoria=<?= urlencode($filtro_categoria) ?>&mes=<?= urlencode($mes_siguiente_param) ?>" class="mini-cal-flecha" title="Mes siguiente">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>

                <div class="mini-cal-dias-semana">
                    <span>LUN</span>
                    <span>MAR</span>
                    <span>MIÉ</span>
                    <span>JUE</span>
                    <span>VIE</span>
                    <span>SÁB</span>
                    <span>DOM</span>
                </div>

                <div class="mini-cal-grilla">
                    <?php
                        // Días del mes anterior para completar la primera semana
                        for ($i = $primer_dia_semana - 2; $i >= 0; $i--) {
                            $dia_prev = $dias_mes_ant - $i;
                            echo "<div class='mini-cal-celda fuera-mes'><span>{$dia_prev}</span></div>";
                        }

                        // Días del mes actual
                        for ($d = 1; $d <= $dias_en_mes; $d++) {
                            $fecha_celda = sprintf('%04d-%02d-%02d', $anio_mes, $mes_num, $d);
                            $es_seleccionado = ($fecha_celda === $fecha_seleccionada);
                            $es_hoy = ($fecha_celda === $fecha_hoy);
                            $clases_celda = 'mini-cal-celda';
                            if ($es_seleccionado) $clases_celda .= ' seleccionado';
                            if ($es_hoy && !$es_seleccionado) $clases_celda .= ' es-hoy';

                            $link_dia = "calendario.php?fecha=" . urlencode($fecha_celda) . "&categoria=" . urlencode($filtro_categoria) . "&mes=" . urlencode($mes_param);
                            $puntos = $puntos_por_dia[$fecha_celda] ?? [];

                            echo "<a href='{$link_dia}' class='{$clases_celda}' title='Ver turnos del {$fecha_celda}'>";
                            echo "<span class='mini-cal-numero'>{$d}</span>";
                            echo "<div class='mini-cal-puntos'>";
                            foreach ($puntos as $p) {
                                echo "<span class='dot-mini dot-{$p}'></span>";
                            }
                            echo "</div>";
                            echo "</a>";
                        }

                        // Días siguientes para completar fila de 7
                        $total_celdas = ($primer_dia_semana - 1) + $dias_en_mes;
                        $resto = $total_celdas % 7;
                        if ($resto > 0) {
                            $faltantes = 7 - $resto;
                            for ($sig = 1; $sig <= $faltantes; $sig++) {
                                echo "<div class='mini-cal-celda fuera-mes'><span>{$sig}</span></div>";
                            }
                        }
                    ?>
                </div>
            </div>

            <div class="leyenda-estados">
                <span class="leyenda-item"><span class="punto-estado punto-libre"></span> Libre</span>
                <span class="leyenda-item"><span class="punto-estado punto-pendiente"></span> Pendiente de Seña</span>
                <span class="leyenda-item"><span class="punto-estado punto-confirmado"></span> Confirmado</span>
                <span class="leyenda-item"><span class="punto-estado punto-pagado"></span> Pagado</span>
            </div>

            <?php if (empty($canchas)): ?>
                <div class="sin-canchas-aviso">
                    No hay canchas registradas en la categoría seleccionada.
                </div>
            <?php else: ?>

                <!-- VISTA ESCRITORIO: TABLA -->
                <div class="vista-escritorio table-responsive-wrapper">
                    <table class="tabla-moderna tabla-calendario">
                        <thead>
                            <tr>
                                <th class="celda-header"><i class="far fa-clock"></i> Hora</th>
                                <?php foreach ($canchas as $cancha): ?>
                                    <th class="celda-header"><?= htmlspecialchars($cancha['tipo_cancha']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($horas_dia as $hd): ?>
                                <?php 
                                    $hora_real = $hd['real'];
                                    $texto_hora = $hd['texto'];
                                ?>
                                <tr>
                                    <td class="celda-hora"><strong><?= $texto_hora ?></strong></td>

                                    <?php foreach ($canchas as $cancha): ?>
                                        <?php 
                                            $idcancha = (int)$cancha['idcancha'];
                                            $nombre_cancha_js = addslashes(htmlspecialchars($cancha['tipo_cancha'], ENT_QUOTES, 'UTF-8'));
                                            
                                            if (isset($matriz_reservas[$idcancha][$hora_real])) {
                                                $entrada = $matriz_reservas[$idcancha][$hora_real];
                                                $es_cont = is_array($entrada) && !empty($entrada['continuacion']);
                                                $reserva = $es_cont ? $entrada['reserva_origen'] : $entrada;

                                                $estado_raw = strtolower(trim((string)($reserva['estado'] ?? 'confirmado')));
                                                if ($estado_raw === 'pendiente') {
                                                    $clase_estado = 'estado-pendiente';
                                                    $etiqueta_estado = 'Pendiente';
                                                } elseif ($estado_raw === 'pagado') {
                                                    $clase_estado = 'estado-pagado';
                                                    $etiqueta_estado = 'Pagado';
                                                } else {
                                                    $clase_estado = 'estado-confirmado';
                                                    $etiqueta_estado = 'Confirmado';
                                                }

                                                $nombre_cliente = trim(($reserva['nombre'] ?? '') . ' ' . ($reserva['apellido'] ?? ''));
                                                if ($nombre_cliente === '') {
                                                    $nombre_cliente = 'Cliente sin nombre';
                                                }
                                                $cliente_esc = htmlspecialchars($nombre_cliente, ENT_QUOTES, 'UTF-8');
                                                $cliente_js = addslashes($cliente_esc);
                                                $telefono_esc = htmlspecialchars(!empty($reserva['telefono']) ? $reserva['telefono'] : 'No registrado', ENT_QUOTES, 'UTF-8');
                                                $telefono_js = addslashes($telefono_esc);
                                                $rango_turno = date('H:i', strtotime($reserva['hora_inicio'])) . ' a ' . date('H:i', strtotime($reserva['hora_fin'])) . ' hs';
                                                $rango_js = addslashes(htmlspecialchars($rango_turno, ENT_QUOTES, 'UTF-8'));
                                                $estado_js = addslashes($etiqueta_estado);

                                                $onclick_js = "verDetalleTurno('{$nombre_cancha_js}', '{$cliente_js}', '{$telefono_js}', '{$rango_js}', '{$estado_js}')";

                                                if ($es_cont) {
                                                    echo "<td class='celda-ocupado celda-continuacion {$clase_estado}' style='cursor: pointer;' onclick=\"{$onclick_js}\" title='Clic para ver datos del turno'>";
                                                    echo "<strong>{$cliente_esc}</strong><br>";
                                                    echo "<small>(Continuación · {$etiqueta_estado})</small>";
                                                    echo "</td>";
                                                } else {
                                                    echo "<td class='celda-ocupado {$clase_estado}' style='cursor: pointer;' onclick=\"{$onclick_js}\" title='Clic para ver datos del turno'>";
                                                    echo "<strong>{$cliente_esc}</strong><br>";
                                                    echo "<small>{$etiqueta_estado}</small>";
                                                    echo "</td>";
                                                }
                                            } else {
                                                $enlace = "reservas.php?cancha_pre={$idcancha}&fecha_pre={$fecha_seleccionada}&hora_pre=" . urlencode($texto_hora);
                                                
                                                echo "<td class='celda-vacia'>";
                                                echo "<a href='{$enlace}' class='celda-libre' title='Registrar reserva en {$texto_hora}'>";
                                                echo "<i class='fas fa-plus-circle'></i> Libre";
                                                echo "</a>";
                                                echo "</td>";
                                            }
                                        ?>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- VISTA MÓVIL: TARJETAS POR CANCHA CON FRANJAS TARDE / NOCHE -->
                <div class="vista-movil-canchas">
                    <?php foreach ($canchas as$cancha): ?>
                        <?php
                            $idcancha = (int)$cancha['idcancha'];
                            $nombre_cancha_js = addslashes(htmlspecialchars($cancha['tipo_cancha'], ENT_QUOTES, 'UTF-8'));
                        ?>
                        <div class="tarjeta-cancha-movil">
                            <div class="cabecera-cancha-movil">
                                <i class="fas fa-futbol"></i>
                                <span><?= htmlspecialchars($cancha['tipo_cancha']) ?></span>
                            </div>

                            <div class="cuerpo-cancha-movil">
                                <div class="bloque-franja-titulo">
                                    <i class="far fa-sun"></i>
                                    <span>Turno Tarde</span>
                                    <small>14:00 - 18:00</small>
                                </div>
                                <div class="grilla-horarios-movil">
                                    <?php foreach ($horas_tarde as$hd): ?>
                                        <?php
                                            $hora_real =$hd['real'];
                                            $texto_hora =$hd['texto'];

                                            if (isset($matriz_reservas[$idcancha][$hora_real])) {$entrada = $matriz_reservas[$idcancha][$hora_real];$es_cont = is_array($entrada) && !empty($entrada['continuacion']);
                                                $reserva =$es_cont ? $entrada['reserva_origen'] :$entrada;

                                                $estado_raw = strtolower(trim((string)($reserva['estado'] ?? 'confirmado')));
                                                if ($estado_raw === 'pendiente') {
                                                    $clase_estado = 'estado-pendiente';$etiqueta_estado = 'Pendiente';
                                                } elseif ($estado_raw === 'pagado') {
                                                    $clase_estado = 'estado-pagado';$etiqueta_estado = 'Pagado';
                                                } else {
                                                    $clase_estado = 'estado-confirmado';$etiqueta_estado = 'Confirmado';
                                                }

                                                $nombre_cliente = trim(($reserva['nombre'] ?? '') . ' ' . ($reserva['apellido'] ?? ''));
                                                if ($nombre_cliente === '') {$nombre_cliente = 'Sin nombre';
                                                }
                                                $cliente_corto = htmlspecialchars(mb_strimwidth($nombre_cliente, 0, 13, '…'), ENT_QUOTES, 'UTF-8');
                                                $cliente_js = addslashes(htmlspecialchars($nombre_cliente, ENT_QUOTES, 'UTF-8'));
                                                $telefono_js = addslashes(htmlspecialchars(!empty($reserva['telefono']) ? $reserva['telefono'] : 'No registrado', ENT_QUOTES, 'UTF-8'));$rango_js = addslashes(htmlspecialchars(date('H:i', strtotime($reserva['hora_inicio'])) . ' a ' . date('H:i', strtotime($reserva['hora_fin'])) . ' hs', ENT_QUOTES, 'UTF-8'));
                                                $estado_js = addslashes($etiqueta_estado);

                                                $onclick_js = "verDetalleTurno('{$nombre_cancha_js}', '{$cliente_js}', '{$telefono_js}', '{$rango_js}', '{$estado_js}')";
                                                ?>
                                                <button type="button" class="slot-hora-movil slot-ocupado <?= $clase_estado ?>" onclick="<?= $onclick_js ?>">
                                                    <span class="slot-hora"><?= $texto_hora ?></span>
                                                    <span class="slot-estado"><?= $es_cont ? 'Cont. · ' . $etiqueta_estado :$etiqueta_estado ?></span>
                                                    <span class="slot-cliente"><?= $cliente_corto ?></span>
                                                </button>
                                                <?php
                                            } else {
                                                $enlace = "reservas.php?cancha_pre={$idcancha}&fecha_pre={$fecha_seleccionada}&hora_pre=" . urlencode($texto_hora);
                                                ?>
                                                <a href="<?= $enlace ?>" class="slot-hora-movil slot-libre">
                                                    <span class="slot-hora"><?= $texto_hora ?></span>
                                                    <span class="slot-estado">Libre</span>
                                                    <span class="slot-cliente">Reservar</span>
                                                </a>
                                                <?php
                                            }
                                        ?>
                                    <?php endforeach; ?>
                                </div>

                                <div class="bloque-franja-titulo">
                                    <i class="far fa-moon"></i>
                                    <span>Turno Noche</span>
                                    <small>19:00 - 00:00</small>
                                </div>
                                <div class="grilla-horarios-movil">
                                    <?php foreach ($horas_noche as$hd): ?>
                                        <?php
                                            $hora_real =$hd['real'];
                                            $texto_hora =$hd['texto'];

                                            if (isset($matriz_reservas[$idcancha][$hora_real])) {$entrada = $matriz_reservas[$idcancha][$hora_real];$es_cont = is_array($entrada) && !empty($entrada['continuacion']);
                                                $reserva =$es_cont ? $entrada['reserva_origen'] :$entrada;

                                                $estado_raw = strtolower(trim((string)($reserva['estado'] ?? 'confirmado')));
                                                if ($estado_raw === 'pendiente') {
                                                    $clase_estado = 'estado-pendiente';$etiqueta_estado = 'Pendiente';
                                                } elseif ($estado_raw === 'pagado') {
                                                    $clase_estado = 'estado-pagado';$etiqueta_estado = 'Pagado';
                                                } else {
                                                    $clase_estado = 'estado-confirmado';$etiqueta_estado = 'Confirmado';
                                                }

                                                $nombre_cliente = trim(($reserva['nombre'] ?? '') . ' ' . ($reserva['apellido'] ?? ''));
                                                if ($nombre_cliente === '') {$nombre_cliente = 'Sin nombre';
                                                }
                                                $cliente_corto = htmlspecialchars(mb_strimwidth($nombre_cliente, 0, 13, '…'), ENT_QUOTES, 'UTF-8');
                                                $cliente_js = addslashes(htmlspecialchars($nombre_cliente, ENT_QUOTES, 'UTF-8'));
                                                $telefono_js = addslashes(htmlspecialchars(!empty($reserva['telefono']) ? $reserva['telefono'] : 'No registrado', ENT_QUOTES, 'UTF-8'));$rango_js = addslashes(htmlspecialchars(date('H:i', strtotime($reserva['hora_inicio'])) . ' a ' . date('H:i', strtotime($reserva['hora_fin'])) . ' hs', ENT_QUOTES, 'UTF-8'));
                                                $estado_js = addslashes($etiqueta_estado);

                                                $onclick_js = "verDetalleTurno('{$nombre_cancha_js}', '{$cliente_js}', '{$telefono_js}', '{$rango_js}', '{$estado_js}')";
                                                ?>
                                                <button type="button" class="slot-hora-movil slot-ocupado <?= $clase_estado ?>" onclick="<?= $onclick_js ?>">
                                                    <span class="slot-hora"><?= $texto_hora ?></span>
                                                    <span class="slot-estado"><?= $es_cont ? 'Cont. · ' . $etiqueta_estado :$etiqueta_estado ?></span>
                                                    <span class="slot-cliente"><?= $cliente_corto ?></span>
                                                </button>
                                                <?php
                                            } else {
                                                $enlace = "reservas.php?cancha_pre={$idcancha}&fecha_pre={$fecha_seleccionada}&hora_pre=" . urlencode($texto_hora);
                                                ?>
                                                <a href="<?= $enlace ?>" class="slot-hora-movil slot-libre">
                                                    <span class="slot-hora"><?= $texto_hora ?></span>
                                                    <span class="slot-estado">Libre</span>
                                                    <span class="slot-cliente">Reservar</span>
                                                </a>
                                                <?php
                                            }
                                        ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        </div> 
    </div>

    <script src="../js/menu_desplegable.js"></script>
    <script src="../js/tabs_calendario.js"></script>
    <script src="../js/detalle_calendario.js"></script>

</body>
</html>
