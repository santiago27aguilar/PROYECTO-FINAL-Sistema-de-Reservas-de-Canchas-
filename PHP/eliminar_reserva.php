<?php
session_start();

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: ../html/login.php");
    exit();
}

$rol_actual = isset($_SESSION['usuario_rol']) ? strtolower(trim($_SESSION['usuario_rol'])) : '';

if ($rol_actual !== 'admin' && $rol_actual !== 'administrador') {
    header("Location: ../html/reservas.php?error=sin_permisos");
    exit();
}

include 'conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $sql = "DELETE FROM reservas WHERE idreservas = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            header("Location: ../html/reservas.php?mensaje=eliminado");
            exit();
        }
    } 
    catch (PDOException $e) {
        // CAPTURA DE ERROR: Verificamos si es una violación de restricción de integridad por clave foránea (Error 1451 / Código 23000)
        if ($e->getCode() == 23000 || strpos($e->getMessage(), '1451') !== false) {
            header("Location: ../html/reservas.php?error=tiene_pago");
            exit();
        } else {
            // Si es cualquier otro error imprevisto de la base de datos
            header("Location: ../html/reservas.php?error=error_general");
            exit();
        }
    }
} else {
    header("Location: ../html/reservas.php");
    exit();
}
?>
