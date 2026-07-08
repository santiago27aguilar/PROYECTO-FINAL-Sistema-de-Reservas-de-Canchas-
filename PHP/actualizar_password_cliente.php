<?php
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $nueva_password = $_POST['nueva_password'];

    try {
        // 1. Buscamos el correo asociado a ese token
        $sql_token = "SELECT correo FROM recuperacion_clave_clientes WHERE token = :token LIMIT 1";
        $stmt_token = $conexion->prepare($sql_token);
        $stmt_token->execute([':token' => $token]);
        $registro = $stmt_token->fetch(PDO::FETCH_ASSOC);

        if ($registro) {
            $correo = $registro['correo'];

            // 2. Encriptamos de forma segura la nueva contraseña
            $password_hasheada = password_hash($nueva_password, PASSWORD_DEFAULT);

            // Iniciamos transacción para asegurar los dos cambios
            $conexion->beginTransaction();

            // 3. Actualizamos la contraseña en la tabla clientes
            $sql_update = "UPDATE clientes SET password = :pass WHERE correo = :correo";
            $stmt_update = $conexion->prepare($sql_update);
            $stmt_update->execute([':pass' => $password_hasheada, ':correo' => $correo]);

            // 4. Borramos el token para que no se pueda reutilizar el enlace
            $sql_delete = "DELETE FROM recuperacion_clave_clientes WHERE correo = :correo";
            $stmt_delete = $conexion->prepare($sql_delete);
            $stmt_delete->execute([':correo' => $correo]);

            $conexion->commit();

            // Éxito completo, lo mandamos al login avisando que ya cambió
            header("Location: ../html/login_cliente.php?mensaje=password_cambiada");
            exit();

        } else {
            // Token inválido o manipulado
            header("Location: ../html/login_cliente.php?error=token_invalido");
            exit();
        }

    } catch (Exception $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        die("Error al actualizar la contraseña: " . $e->getMessage());
    }
} else {
    header("Location: ../html/login_cliente.php");
    exit();
}
?>
