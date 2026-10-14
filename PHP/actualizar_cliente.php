<?php
    session_start();
    // Seguridad: Solo usuarios logueados pueden editar
    if(!isset($_SESSION['usuario_nombre'])){ exit("Acceso denegado"); }

    include 'conexion.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $dni = $_POST['dni'];
        $telefono = $_POST['telefono'];
        $correo = $_POST['correo'];

        try {
            $sql = "UPDATE clientes SET nombre = :n, apellido = :a, dni = :d, telefono = :t, correo = :c WHERE idclientes = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':n' => $nombre,':a' => $apellido,':d' => $dni,':t' => $telefono,':c' => $correo,':id' => $id
        ]);

            // Redirigimos con mensaje de éxito
            header("Location: ../html/inicio.php?mensaje=modificado");
            exit();

        } catch (PDOException $e) {
            die("Error al actualizar: " . $e->getMessage());
        }
    }
?>
