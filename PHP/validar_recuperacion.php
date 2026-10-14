<?php
session_start();
include 'conexion.php'; 
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_usuario = trim($_POST['nombre']);

    try {
        // Consulta basada en tu MER: tabla 'usuario' y columna 'nombre'
        $sql = "SELECT idusuario FROM usuario WHERE nombre = :nombre";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre_usuario);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Guardamos el ID del administrador/recepcionista
            $_SESSION['id_recuperar_admin'] = $user['idusuario'];
            header("Location: ../html/nuevo_password.php"); 
            exit();
        } else {
            header("Location: ../html/recuperar_password.php?error=1");
            exit();
        }
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
