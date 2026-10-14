<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- EFECTO DE CARGA ---
    sleep(4);
    // -----------------------

    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Encriptamos la contraseña
    $password_hasheada = password_hash($password, PASSWORD_DEFAULT);

    try {
        $check = $conexion->prepare("SELECT idclientes FROM clientes WHERE dni = :dni OR correo = :correo");
        $check->execute([':dni' => $dni, ':correo' => $correo]);

        if ($check->rowCount() > 0) {
            header("Location: ../html/registro_cliente.php?error=existe");
            exit();
        }

        // Insertamos el cliente (dejándolo Activo por defecto y sin el token)
        $sql = "INSERT INTO clientes (nombre, apellido, dni, telefono, correo, password, estado) VALUES (:nombre, :apellido, :dni, :telefono, :correo, :password, 'Activo')";
        $stmt = $conexion->prepare($sql);

        if ($stmt->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':dni' => $dni,
            ':telefono' => $telefono,
            ':correo' => $correo,
            ':password' => $password_hasheada
        ])) {
            // Si se guarda correctamente, lo mandamos al login
            header("Location: ../html/login_cliente.php?mensaje=registrado");
            exit();
        }
    } catch (PDOException $e) {
        echo "Error al registrar: " . $e->getMessage();
    }
}
?>
