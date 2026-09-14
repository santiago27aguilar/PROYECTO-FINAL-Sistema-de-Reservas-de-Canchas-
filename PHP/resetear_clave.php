<?php
require_once 'conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // NUEVO: Hasheamos la clave antes de guardarla en la BD
    $nueva_clave_hasheada = password_hash('1234', PASSWORD_DEFAULT); 

    try {
        $stmt = $conexion->prepare("UPDATE usuario SET password = ? WHERE idusuario = ?");
        // Usamos la variable hasheada en la consulta
        $stmt->execute([$nueva_clave_hasheada, $id]);
        
        header("Location: ../html/personal.php?mensaje=clave_reseteada");
        exit(); // Siempre es buena práctica poner exit() después de un header
    } catch(PDOException $e) {
        header("Location: ../html/personal.php?error=fallo_db");
        exit();
    }
}
?>
