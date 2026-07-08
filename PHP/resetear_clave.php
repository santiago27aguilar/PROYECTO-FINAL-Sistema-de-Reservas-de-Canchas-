<?php
require_once 'conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $nueva_clave = '1234'; // La contraseña por defecto

    try {
        $stmt = $conexion->prepare("UPDATE usuario SET password = ? WHERE idusuario = ?");
        $stmt->execute([$nueva_clave, $id]);
        
        header("Location: ../pages/personal.php?mensaje=clave_reseteada");
    } catch(PDOException $e) {
        header("Location: ../pages/personal.php?error=fallo_db");
    }
}
?>
