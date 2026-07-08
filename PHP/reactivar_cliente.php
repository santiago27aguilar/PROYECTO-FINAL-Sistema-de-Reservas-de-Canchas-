<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_rol']) || !in_array(strtolower($_SESSION['usuario_rol']), ['duenio', 'dueño'])) {
    header("Location: ../html/inicio.php?error=sin_permisos");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $conexion->prepare("UPDATE usuario SET estado = 'Activo' WHERE idusuario = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header("Location: ../html/personal.php?mensaje=reactivado");
        exit();
    } catch (PDOException $e) {
        header("Location: ../html/personal.php?error=fallo_db");
        exit();
    }
}
?>
