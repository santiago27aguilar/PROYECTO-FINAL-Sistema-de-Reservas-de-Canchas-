<?php
session_start();

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: ../html/login.html");
    exit();
}

$rol_actual = isset($_SESSION['usuario_rol']) ? strtolower(trim($_SESSION['usuario_rol'])) : '';
if ($rol_actual !== 'admin' && $rol_actual !== 'administrador') {
    header("Location: ../html/canchas.php?error=sin_permisos");
    exit();
}

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = $_POST['tipo_cancha'];
    $precio = (int)$_POST['precio_hora'];

    if ($precio <= 0) {
        header("Location: ../html/canchas.php?error=precio_invalido");
        exit();
    }

    try {
        
        $check = $conexion->prepare("SELECT idcancha FROM cancha WHERE tipo_cancha = :tipo");
        $check->execute([':tipo' => $tipo]);

        if ($check->rowCount() > 0) {
            header("Location: ../html/canchas.php?error=cancha_duplicada");
            exit();
        }

        $sql = "INSERT INTO cancha (tipo_cancha, precio_hora) VALUES (:tipo, :precio)";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt->execute([':tipo' => $tipo, ':precio' => $precio])) {
            header("Location: ../html/canchas.php?mensaje=registrado");
            exit();
        }

    } catch (PDOException $e) {
        echo "Error en la base de datos: " . $e->getMessage();
    }
} else {
    header("Location: ../html/canchas.php");
    exit();
}
?>
