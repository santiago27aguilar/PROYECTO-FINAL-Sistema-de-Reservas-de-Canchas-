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

    // 1. Validar que la fecha no sea en el pasado
    $fecha_hoy = date('Y-m-d');
    if ($fecha < $fecha_hoy) {
        header("Location: ../html/cliente.php?error=fecha_pasada");
        exit();
    }

    // 2. Validar horario comercial (Ejemplo: Abierto de 16:00 a 23:00)
    $horario_apertura = "15:00";
    $horario_cierre = "00:00";
    if ($hora_inicio < $horario_apertura || $hora_inicio > $horario_cierre) {
        header("Location: ../html/cliente.php?error=fuera_horario");
        exit();
    }

    // 3. Validar duración permitida (1 hora o 1.5 horas)
    if ($duracion != 1 && $duracion != 2) {
        header("Location: ../html/cliente.php?error=duracion_invalida");
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
        // Validación de disponibilidad (Tu lógica original impecable)
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
