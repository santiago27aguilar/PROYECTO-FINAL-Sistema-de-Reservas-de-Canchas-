<?php
// 1. EL CANDADO: Siempre iniciar sesión
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../html/login.php");
    exit();
}

// 2. PERMISOS: Solo el admin/dueño puede anular pagos
$rol_actual = isset($_SESSION['usuario_rol']) ? strtolower(trim($_SESSION['usuario_rol'])) : '';

if (!in_array($rol_actual, ['admin', 'administrador', 'duenio', 'dueño'])) {
    header("Location: ../html/pagos.php?error=sin_permisos");
    exit();
}

include 'conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // 3. REBOBINADO: Buscar de qué reserva era este pago
        $sql_info = "SELECT reservas_idreservas FROM pagos WHERE idpagos = :id";
        $stmt_info = $conexion->prepare($sql_info);
        $stmt_info->execute([':id' => $id]);
        $reserva_id = $stmt_info->fetchColumn();

        if ($reserva_id) {
            // Rebobinado: Volver la reserva a estado 'Pendiente' (o 'Confirmado' según tu regla)
            $sql_upd_reserva = "UPDATE reservas SET estado = 'Pendiente' WHERE idreservas = :reserva";
            $stmt_upd_reserva = $conexion->prepare($sql_upd_reserva);
            $stmt_upd_reserva->execute([':reserva' => $reserva_id]);
            
            // ATENCIÓN: Se eliminó el rebobinado de "Cancha = Ocupado" para no romper el borrado lógico anterior.
        }

        // 4. BORRADO LÓGICO: Anulamos el pago en vez de hacer DELETE
        $sql_anular = "UPDATE pagos SET estado = 'Anulado' WHERE idpagos = :id";
        $stmt_anular = $conexion->prepare($sql_anular);
        
        if ($stmt_anular->execute([':id' => $id])) {
            header("Location: ../html/pagos.php?mensaje=anulado");
            exit();
        }
    } 
    catch (PDOException $e) {
        echo "Error al anular: " . $e->getMessage();
    }
} else {
    header("Location: ../html/pagos.php");
    exit();
}
?>
