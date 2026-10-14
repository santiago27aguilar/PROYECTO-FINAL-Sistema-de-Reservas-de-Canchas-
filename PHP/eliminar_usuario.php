<?php
// 1. Iniciamos sesión y ponemos el candado de seguridad
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_rol'])) {
    header("Location: ../html/login.php");
    exit();
}

$rol_actual = strtolower($_SESSION['usuario_rol']);
if (!in_array($rol_actual, ['duenio', 'dueño'])) {
    header("Location: ../html/inicio.php?error=sin_permisos");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $admin_id = $_SESSION['id_usuario']; // Rescatamos quién está haciendo el click
    
    // Evitar que el dueño se borre a sí mismo por accidente
    if ($id == $admin_id) {
        header("Location: ../html/personal.php?error=no_borrar_admin");
        exit();
    }

    try {
        // Borrado Lógico + Auditoría completa (Quién y Cuándo)
        $stmt = $conexion->prepare("UPDATE usuario SET estado = 'Inactivo', modificado_por = :admin, fecha_modificacion = NOW() WHERE idusuario = :id");
        $stmt->bindParam(':admin', $admin_id);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header("Location: ../html/personal.php?mensaje=eliminado");
        exit();
        
    } catch (PDOException $e) {
        header("Location: ../html/personal.php?error=fallo_db");
        exit();
    }
}
?>
