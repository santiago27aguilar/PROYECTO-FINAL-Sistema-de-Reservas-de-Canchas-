<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Encriptamos la contraseña por seguridad
    $password_hasheada = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Verificamos si el DNI o el Correo ya existen
        $check = $conexion->prepare("SELECT idclientes FROM clientes WHERE dni = :dni OR correo = :correo");
        $check->bindParam(':dni', $dni);
        $check->bindParam(':correo', $correo);
        $check->execute();

        if ($check->rowCount() > 0) {
            // Si ya existe, lo mandamos de vuelta con un error
            header("Location: ../html/registro_cliente.php?error=existe");
            exit();
        }

        // Si no existe, lo insertamos en la tabla
        $sql = "INSERT INTO clientes (nombre, apellido, dni, telefono, correo, password) VALUES (:nombre, :apellido, :dni, :telefono, :correo, :password)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':dni', $dni);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':password', $password_hasheada);

        if ($stmt->execute()) {
            // Todo salió bien, lo mandamos al login para que ingrese
            header("Location: ../html/login_cliente.php?mensaje=registrado");
            exit();
        }
    } catch (PDOException $e) {
        echo "Error al registrar: " . $e->getMessage();
    }
}
?>
