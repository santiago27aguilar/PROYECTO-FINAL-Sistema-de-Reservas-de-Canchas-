<?php
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nueva_pass = $_POST['nueva_pass'];
    $token = $_POST['token']; 

    try {
        // 1. Buscamos el correo asociado al token
        $stmt = $conexion->prepare("SELECT correo FROM recuperacion_de_clave WHERE token = :token");
        $stmt->execute([':token' => $token]);
        $registro = $stmt->fetch();

        if ($registro) {
            $correo = $registro['correo'];

            // 2. Encriptamos la nueva contraseña antes de guardarla
            $pass_hash = password_hash($nueva_pass, PASSWORD_DEFAULT);

            // 3. Actualizamos la base de datos con la versión encriptada
            $sql = "UPDATE usuario SET password = :pass WHERE correo = :correo";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':pass' => $pass_hash, ':correo' => $correo]);

            // 4. Borramos el token
            $stmt_delete = $conexion->prepare("DELETE FROM recuperacion_de_clave WHERE token = :token");
            $stmt_delete->execute([':token' => $token]);

            header("Location: ../html/login.php?cambio=exito");
            exit();
        } else {
            die("Error: El token no es válido o ya fue utilizado.");
        }
    } catch (Exception $e) {
        die("Error al actualizar la contraseña: " . $e->getMessage());
    }
}
?>
