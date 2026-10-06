<?php
    session_start();
    if(!isset($_SESSION['usuario_nombre'])){
        header('Location: login.php');
        exit();
    }
    include '../php/conexion.php'; // Agregamos la conexión arriba para usarla en los filtros

    $rol_usuario = $_SESSION['usuario_rol'];

    // CAPTURAR FILTROS (Misma lógica que en Clientes/Pagos)
    $f_tipo = isset($_GET['f_tipo']) ? $_GET['f_tipo'] : 'Todos';
    $f_estado = isset($_GET['f_estado']) ? $_GET['f_estado'] : 'Activa';

    // ARMAR CONDICIONES
    $where = " WHERE 1=1 ";
    $params = [];

    if ($f_tipo !== 'Todos') {
        $where .= " AND tipo_cancha LIKE :tipo ";
        $params[':tipo'] = "%$f_tipo%";
    }

    if ($f_estado === 'Activa') {
        $where .= " AND (estado = 'Activa' OR estado IS NULL) ";
    } elseif ($f_estado === 'Inactiva') {
        $where .= " AND estado = 'Inactiva' ";
    }

    // CONSULTA PRINCIPAL APLICANDO FILTROS
    try {
        $sql_canchas = "SELECT * FROM cancha $where ORDER BY estado ASC, tipo_cancha ASC";
        $stmt_canchas = $conexion->prepare($sql_canchas);
        $stmt_canchas->execute($params);
        $resultado_canchas = $stmt_canchas->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // Si la columna estado no existe, hacemos una consulta fallback básica
        $resultado_canchas = $conexion->query("SELECT * FROM cancha")->fetchAll(PDO::FETCH_ASSOC);
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Canchas - Planeta de Futbol</title>
    <?php include 'head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_canchas.css?v=5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    
    <div class="navbar">
        <div class="menu-toggle" id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>

        <div class="nav-links" id="nav-links">

            <!-- COSAS QUE ***SOLO*** VE EL DUEÑO -->
            <?php if(isset($_SESSION['usuario_rol']) && in_array(strtolower($_SESSION['usuario_rol']), ['duenio', 'dueño'])): ?>
                <a href="dashboard.php">Tablero</a>
                <a href="personal.php">Personal</a>
                <a href="calendario.php">Calendario</a> 
            <?php endif; ?>

            <!-- 👥 COSAS QUE VEN TODOS -->
            <a href="inicio.php">Clientes</a>
            <a href="reservas.php">Reservas</a>
            <a href="canchas.php" class="link-activo">Canchas</a> 
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

        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
            <div class="alerta alerta-exito">Cancha suspendida correctamente</div>
        <?php endif; ?>
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'registrado'): ?>
            <div class="alerta alerta-exito">¡Cancha registrada con éxito!</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'sin_permisos'): ?>
            <div class="alerta alerta-error">No tienes permisos para realizar esta acción</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'tiene_reservas'): ?>
            <div class="alerta alerta-error">Error: La cancha tiene reservas futuras pendientes o confirmadas. Cancelalas primero.</div>
        <?php endif; ?>
        
        <div class="card-blanca">
            
            <div class="header-titulo-cancha">
                <div class="textos-cancha">
                    <h2>REGISTRAR NUEVA CANCHA</h2>
                    <p class="subtitulo">(Futbol 5 - Futbol 7 - Padel)</p>
                </div>
                <img src="../img/logo-registrar.png" alt="Icono Cancha" class="icono-cancha">
            </div>
            
            <form action="../php/registrar_cancha.php" method="POST" class="reserva-form">
                <div class="grid-split">
                    <div class="seccion-imagenes">
                        <img src="../img/futboll.png" alt="Foto Cancha 1" class="img-cuadro">
                        <img src="../img/padell.png" alt="Foto Cancha 2" class="img-cuadro">
                    </div>

                    <div class="seccion-form">
                        <div class="form-group">
                            <label>Tipo de CANCHA: <span class="asterisco">*</span></label>
                            <select name="tipo_cancha" required class="input-form">
                                <option value="">> Elije una cancha <</option>
                                <option value="Futbol 5 - Cancha 1">Futbol 5 - Cancha 1</option>
                                <option value="Futbol 5 - Cancha 2">Futbol 5 - Cancha 2</option>
                                <option value="Futbol 5 - Cancha 3">Futbol 5 - Cancha 3</option>
                                <option value="Futbol 7 - Cancha 1">Futbol 7 - Cancha 1</option>
                                <option value="Futbol 7 - Cancha 2">Futbol 7 - Cancha 2</option>
                                <option value="Futbol 7 - Cancha 3">Futbol 7 - Cancha 3</option>
                                <option value="Padel - Cancha 1">Padel - Cancha 1</option>
                                <option value="Padel - Cancha 2">Padel - Cancha 2</option>
                                <option value="Padel - Cancha 3">Padel - Cancha 3</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Precio por HORA: <span class="asterisco">*</span></label>
                            <input type="number" name="precio_hora" onkeydown="return event.keyCode !== 69" placeholder="Ej: 5000" autocomplete="off" required class="input-form">
                        </div>

                        <button type="submit" class="btn-guardar btn-full btn-margen">GUARDAR CANCHA</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-container">
            
            <!-- TÍTULO (Preparado con el mismo layout por si luego querés agregar los botones de exportar acá) -->
            <div class="header-lista">
                <h2>LISTADO DE LAS CANCHAS</h2>
            </div>
            
            <form action="canchas.php" method="GET" id="formFiltros">
                
                <!-- FILTROS AGRUPADOS ESTILO UNIFICADO -->
                <div class="barra-filtros">
                    
                    <div class="grupo-filtro">
                        <label>Tipo de Cancha:</label>
                        <select name="f_tipo" onchange="document.getElementById('formFiltros').submit();">
                            <option value="Todos" <?php echo ($f_tipo == 'Todos') ? 'selected' : ''; ?>>Todas las canchas</option>
                            <option value="Futbol 5" <?php echo ($f_tipo == 'Futbol 5') ? 'selected' : ''; ?>>Fútbol 5</option>
                            <option value="Futbol 7" <?php echo ($f_tipo == 'Futbol 7') ? 'selected' : ''; ?>>Fútbol 7</option>
                            <option value="Padel" <?php echo ($f_tipo == 'Padel') ? 'selected' : ''; ?>>Pádel</option>
                        </select>
                    </div>

                    <div class="grupo-filtro">
                        <label>Estado de la Cancha:</label>
                        <select name="f_estado" onchange="document.getElementById('formFiltros').submit();">
                            <option value="Activa" <?php echo ($f_estado == 'Activa') ? 'selected' : ''; ?>>Activas</option>
                            <option value="Inactiva" <?php echo ($f_estado == 'Inactiva') ? 'selected' : ''; ?>>Suspendidas</option>
                            <option value="Todos" <?php echo ($f_estado == 'Todos') ? 'selected' : ''; ?>>Todos los estados</option>
                        </select>
                    </div>
                    
                    <div class="grupo-botones-filtro">
                        <?php if($f_tipo !== 'Todos' || $f_estado !== 'Activa'): ?>
                            <a href="canchas.php" class="btn-limpiar"><i class="fas fa-times"></i> Limpiar</a>
                        <?php endif; ?>
                    </div>
                </div>

            </form>

            <div class="table-responsive-wrapper">
                <table class="tabla-moderna">
                    <thead>
                        <tr>
                            <th>Tipo de CANCHA</th>
                            <th>Precio por HORA</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resultado_canchas)): ?>
                            <tr><td colspan="4" style="text-align: center;">No se encontraron canchas para este filtro.</td></tr>
                        <?php else: ?>
                            <?php foreach ($resultado_canchas as $fila) { 
                                $estado_actual = isset($fila['estado']) ? $fila['estado'] : 'Activa';
                                $es_inactiva = (strtolower($estado_actual) === 'inactiva');
                            ?>
                                <tr style="<?php echo $es_inactiva ? 'opacity: 0.6;' : ''; ?>">
                                    <td><strong><?php echo htmlspecialchars($fila['tipo_cancha']); ?></strong></td>
                                    <td>$<?php echo number_format($fila['precio_hora'], 2); ?></td>
                                    <td style="font-weight:bold; color: <?php echo $es_inactiva ? '#dc3545' : '#28a745'; ?>">
                                        <?php echo strtoupper($estado_actual); ?>
                                    </td>
                                    <td>
                                        <div class="acciones-flex">
                                            <?php if (in_array(strtolower($_SESSION['usuario_rol']), ['admin', 'administrador', 'duenio', 'dueño'])): ?>
                                                <?php if (!$es_inactiva): ?>
                                                    <a href="../php/eliminar_cancha.php?id=<?php echo $fila['idcancha']; ?>" class="btn-eliminar" onclick="return confirm('¿Deseas suspender esta cancha?')">Suspender</a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="sin-permisos">Sin Permisos</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../js/menu_desplegable.js"></script>
    <script src="../js/alerta_cliente.js"></script>

</body>
</html>
