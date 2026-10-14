<?php
// 1. EL CANDADO: Siempre iniciar sesión primero
session_start();

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: ../html/login.php");
    exit();
}

// 2. VERIFICAR ROL: Solo dueño o admin puede borrar canchas
$rol_actual = isset($_SESSION['usuario_rol']) ? strtolower(trim($_SESSION['usuario_rol'])) : '';

if (!in_array($rol_actual, ['admin', 'administrador', 'duenio', 'dueño'])) {
    header("Location: ../html/canchas.php?error=sin_permisos");
    exit();
}

// 3. CONEXIÓN
include 'conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // 4. CHEQUEO: ¿Esta cancha tiene reservas activas (pendientes o confirmadas)?
        // (Modificamos para que solo salte error si hay reservas FUTURAS o pendientes, 
        // si son reservas pasadas o canceladas, se debería poder suspender la cancha).
        $sql_check = "SELECT COUNT(*) FROM reservas WHERE cancha_idcancha = :id AND estado IN ('Pendiente', 'Confirmado')";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->execute([':id' => $id]);
        
        $cantidad_reservas = $stmt_check->fetchColumn();

        if ($cantidad_reservas > 0) {
            header("Location: ../html/canchas.php?error=tiene_reservas");
            exit();
        }

        // 5. BORRADO LÓGICO: En vez de DELETE, hacemos UPDATE.
        $sql = "UPDATE cancha SET estado = 'Inactiva' WHERE idcancha = :id";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt->execute([':id' => $id])) {
            header("Location: ../html/canchas.php?mensaje=eliminado");
            exit();
        }
    } 
    catch(PDOException $e) {
        echo "Error al eliminar: " . $e->getMessage();
    }
} else {
    header("Location: ../html/canchas.php");
    exit();
}
?>
