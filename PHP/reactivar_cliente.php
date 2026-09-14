<?php
session_start();
include 'conexion.php';

// 1. Agregamos al admin y administrador en los permisos
if (!isset($_SESSION['usuario_rol']) || !in_array(strtolower($_SESSION['usuario_rol']), ['duenio', 'dueño', 'admin', 'administrador'])) {
    header("Location: ../html/inicio.php?error=sin_permisos");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        // 2. Corregimos la tabla (ahora apunta a 'clientes' y a 'idclientes')
        $stmt = $conexion->prepare("UPDATE clientes SET estado = 'Activo' WHERE idclientes = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // 3. Redirigimos correctamente a inicio.php (donde está el panel de clientes)
        header("Location: ../html/inicio.php?mensaje=reactivado");
        exit();
    } catch (PDOException $e) {
        header("Location: ../html/inicio.php?error=fallo_db");
        exit();
    }
}
?>
