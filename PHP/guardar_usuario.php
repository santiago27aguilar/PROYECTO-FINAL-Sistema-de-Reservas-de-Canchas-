<?php
// 1. Llamamos a tu conexión
require_once 'conexion.php';

// 2. Verificamos que los datos vengan del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recibimos los datos y los limpiamos de espacios extra
    $nombre = trim($_POST['nombre']);
    $password = trim($_POST['password']);
    $rol = trim($_POST['rol']);

    // Validamos que no vengan vacíos
    if (!empty($nombre) && !empty($password) && !empty($rol)) {
        
        try {
            // A. Primero verificamos que el usuario no exista ya en la base
            $stmtCheck = $conexion->prepare("SELECT idusuario FROM usuario WHERE nombre = :nombre");
            $stmtCheck->bindParam(':nombre', $nombre);
            $stmtCheck->execute();

            if ($stmtCheck->rowCount() > 0) {
                // Si ya existe, lo devolvemos con un error
                header("Location: ../html/personal.php?error=duplicado");
                exit();
            }

            // B. Encriptamos la contraseña (NUNCA se guarda en texto plano)
            $passwordEncriptada = password_hash($password, PASSWORD_DEFAULT);

            // C. Preparamos la consulta para insertar en tu tabla 'usuario'
            $stmtInsert = $conexion->prepare("INSERT INTO usuario (nombre, password, rol) VALUES (:nombre, :password, :rol)");
            
            $stmtInsert->bindParam(':nombre', $nombre);
            $stmtInsert->bindParam(':password', $passwordEncriptada);
            $stmtInsert->bindParam(':rol', $rol);

            // Ejecutamos y volvemos a la pantalla con mensaje de éxito
            if ($stmtInsert->execute()) {
                header("Location: ../html/personal.php?mensaje=exito");
                exit();
            }

        } catch(PDOException $e) {
            die("Hubo un error al guardar el empleado: " . $e->getMessage());
        }
    } else {
        // Si faltaron datos
        header("Location: ../html/personal.php?error=vacios");
        exit();
    }
} else {
    // Si alguien intenta entrar a este archivo directamente por la URL
    header("Location: ../html/personal.php");
    exit();
}
?>
