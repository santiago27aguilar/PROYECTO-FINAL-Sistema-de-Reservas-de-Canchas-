<?php
// 1. SIEMPRE iniciar sesión primero
session_start();

// 2. VERIFICAR si el usuario está logueado
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: ../html/login.php");
    exit();
}

$rol_actual = isset($_SESSION['usuario_rol']) ? strtolower(trim($_SESSION['usuario_rol'])) : '';

// 3. VERIFICAR PERMISOS (Corregido para incluir al dueño)
if (!in_array($rol_actual, ['admin', 'administrador', 'duenio', 'dueño'])) {
    header("Location: ../html/inicio.php?error=sin_permisos");
    exit();
}

include 'conexion.php';

// 4. VERIFICAR que el ID llegue por la URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // --- PUNTO 6: BORRADO LÓGICO ---
        // Hacemos un UPDATE para cambiar el estado en vez de borrar el registro
        $sql = "UPDATE clientes SET estado = 'Inactivo' WHERE idclientes = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Redirigimos con el mismo mensaje de éxito para aprovechar tus alertas verdes
        header("Location: ../html/inicio.php?mensaje=eliminado");
        exit(); 
    } 
    catch (PDOException $e) {
        // Si hay un error de conexión o sintaxis
        die("Error crítico: No se pudo cambiar el estado del registro. " . $e->getMessage());
    }
} else {
    // Si no hay ID, volvemos al inicio
    header("Location: ../html/inicio.php");
    exit();
}
?>
