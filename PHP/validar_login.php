<?php
include 'conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario = trim($_POST['user']);
    $password = trim($_POST['pass']);

    try {
        // Seleccionamos los datos incluyendo el password guardado en la BD
        $sql = "SELECT idusuario, nombre, password, rol FROM usuario WHERE nombre = :u";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':u', $usuario);
        $stmt->execute();

        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        // AJUSTE AQUÍ: Usamos password_verify en lugar de ===
        if ($datos && password_verify($password, $datos['password'])) {
            
            session_regenerate_id(true);

            $_SESSION['id_usuario'] = $datos['idusuario'];
            $_SESSION['usuario_nombre'] = $datos['nombre'];
            $_SESSION['usuario_rol'] = $datos['rol'];

            // --- REDIRECCIÓN INTELIGENTE ---
            $rol_actual = strtolower($datos['rol']);

            if ($rol_actual === 'duenio' || $rol_actual === 'dueño') {
                header("Location: ../html/dashboard.php");
            } else {
                header("Location: ../html/inicio.php");
            }
            exit(); 
            
        } else {
            // El usuario o la contraseña son incorrectos
            header("Location: ../html/login.php?error=incorrecto");
            exit();
        }
    } 
    catch (PDOException $e) {
        die("ERROR TÉCNICO: " . $e->getMessage());
    }
} else {
    header("Location: ../html/login.php");
    exit();
}
?>
