<?php
session_start();
include 'conexion.php';

// Si no está logueado, lo pateamos al login
if (!isset($_SESSION['id_cliente'])) {
    header("Location: ../html/login_cliente.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cliente = $_SESSION['id_cliente'];
    $id_cancha = $_POST['idcancha'];
    $fecha = $_POST['fecha_reserva'];
    $hora_inicio = $_POST['hora_inicio'];
    $duracion = $_POST['duracion'];

    // =======================================================
    // FASE 3: REGLAS DE NEGOCIO Y VALIDACIONES ESTRICTAS
    // =======================================================

    // FUNDAMENTAL: Configurar la zona horaria para que la hora coincida con la real
    date_default_timezone_set('America/Argentina/Tucuman');

    // 1. Validar que la reserva (fecha y hora combinadas) no sea en el pasado
    $fecha_hora_reserva = new DateTime($fecha . ' ' . $hora_inicio);
    $ahora = new DateTime(); 

    if ($fecha_hora_reserva <= $ahora) {
        header("Location: ../html/cliente.php?error=fecha_pasada");
        exit();
    }

    // 2. Validar horario comercial
    $horario_apertura = "14:00";
    $horario_cierre = "23:59";
    if ($hora_inicio < $horario_apertura || $hora_inicio > $horario_cierre) {
        header("Location: ../html/cliente.php?error=fuera_horario");
        exit();
    }

    // 3. Validar duración permitida (1 hora o 2 horas)
    if ($duracion != 1 && $duracion != 2) {
        header("Location: ../html/cliente.php?error=duracion_invalida");
        exit();
    }

    // 4. NUEVO: Validar límite máximo de 2 turnos por día por cliente
    $sql_limite = "SELECT COUNT(*) as total_turnos FROM reservas 
                   WHERE clientes_idclientes = :id_cli 
                   AND DATE(hora_inicio) = :fecha 
                   AND estado != 'Cancelado'";
    $stmt_limite = $conexion->prepare($sql_limite);
    $stmt_limite->execute([
        ':id_cli' => $id_cliente,
        ':fecha' => $fecha
    ]);
    $resultado_limite = $stmt_limite->fetch(PDO::FETCH_ASSOC);

    if ($resultado_limite['total_turnos'] >= 2) {
        // Si ya tiene 2 (o más) para esa fecha, lo rebotamos
        header("Location: ../html/cliente.php?error=limite_diario");
        exit();
    }

    // =======================================================
    // PROCESAMIENTO DE LA RESERVA
    // =======================================================

    $inicio_timestamp = strtotime("$fecha $hora_inicio");
    $fin_timestamp    = $inicio_timestamp + ($duracion * 3600);
    $hora_inicio_db   = date('Y-m-d H:i:s', $inicio_timestamp);
    $hora_fin_db      = date('Y-m-d H:i:s', $fin_timestamp);

    try {
        // Validación de disponibilidad
        $sql_dispo = "SELECT COUNT(*) as ocupado FROM reservas 
                      WHERE cancha_idcancha = :id_can AND estado != 'Cancelado' 
                      AND ((hora_inicio < :fin AND hora_fin > :inicio))";
        
        $stmt_dispo = $conexion->prepare($sql_dispo);
        $stmt_dispo->execute([':id_can' => $id_cancha, ':inicio' => $hora_inicio_db, ':fin' => $hora_fin_db]);
        $resultado = $stmt_dispo->fetch(PDO::FETCH_ASSOC);

        if ($resultado['ocupado'] > 0) {
            header("Location: ../html/cliente.php?error=ocupado");
            exit();
        }

        // Iniciar el guardado seguro
        $conexion->beginTransaction();

        $conexion->exec("INSERT IGNORE INTO usuario (idusuario, nombre, password, rol) VALUES (1, 'Admin Sistema', '1234', 'Administrador')");

        $sql = "INSERT INTO reservas (hora_inicio, hora_fin, estado, usuario_idusuario, cancha_idcancha, clientes_idclientes) 
                VALUES (:inicio, :fin, 'Pendiente', 1, :id_can, :id_cli)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':inicio' => $hora_inicio_db,
            ':fin' => $hora_fin_db,
            ':id_can' => $id_cancha,
            ':id_cli' => $id_cliente
        ]);
        
        // ¡Se cierra la transacción!
        $conexion->commit();
        
        // Lo mandamos a la pantalla de éxito
        header("Location: ../html/cliente.php?reserva=ok");
        exit();

    } catch (Exception $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        die("Error de Base de Datos: " . $e->getMessage());
    }
}
?>
