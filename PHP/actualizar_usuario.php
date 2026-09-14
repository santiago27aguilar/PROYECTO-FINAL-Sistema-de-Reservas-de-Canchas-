<?php
// 1. Llamamos a tu conexión
require_once 'conexion.php';

// 2. Verificamos que los datos vengan del formulario (ventana modal)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recibimos los datos y los limpiamos de espacios extra
    $idusuario = trim($_POST['idusuario']);
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $rol = trim($_POST['rol']);

    // Validamos que ningún campo esté vacío
    if (!empty($idusuario) && !empty($nombre) && !empty($correo) && !empty($rol)) {
        
        try {
            // A. Verificamos que el nuevo nombre de usuario no esté siendo usado por OTRA persona
            $stmtCheck = $conexion->prepare("SELECT idusuario FROM usuario WHERE nombre = :nombre AND idusuario != :idusuario");
            $stmtCheck->bindParam(':nombre', $nombre);
            $stmtCheck->bindParam(':idusuario', $idusuario);
            $stmtCheck->execute();

            if ($stmtCheck->rowCount() > 0) {
                // Si el nombre ya lo usa otro, lo devolvemos con error
                header("Location: ../html/personal.php?error=duplicado");
                exit();
            }

            // B. Preparamos la consulta UPDATE para modificar solo a este empleado
            $stmtUpdate = $conexion->prepare("UPDATE usuario SET nombre = :nombre, correo = :correo, rol = :rol WHERE idusuario = :idusuario");
            
            $stmtUpdate->bindParam(':nombre', $nombre);
            $stmtUpdate->bindParam(':correo', $correo);
            $stmtUpdate->bindParam(':rol', $rol);
            $stmtUpdate->bindParam(':idusuario', $idusuario);

            // C. Ejecutamos y volvemos a la pantalla con el mensaje de éxito
            if ($stmtUpdate->execute()) {
                header("Location: ../html/personal.php?mensaje=actualizado");
                exit();
            }

        } catch(PDOException $e) {
            die("Hubo un error al actualizar el empleado: " . $e->getMessage());
        }
    } else {
        // Si mandaron el formulario por la mitad
        header("Location: ../html/personal.php?error=vacios");
        exit();
    }
} else {
    // Si alguien intenta entrar a este archivo directamente por la URL
    header("Location: ../html/personal.php");
    exit();
}
?>
