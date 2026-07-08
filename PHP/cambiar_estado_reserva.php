<?php
session_start();
require 'conexion.php';

// 1. Verificamos que sea alguien del personal quien intenta hacer esto
if (!isset($_SESSION['usuario_rol'])) {
    header("Location: ../html/login.php");
    exit();
}

// 2. Comprobamos que nos estén enviando un ID y una Acción por la URL
if (isset($_GET['id']) && isset($_GET['accion'])) {
    $id_reserva = $_GET['id'];
    $accion = $_GET['accion'];

    try {
        // 3. Definimos cuál va a ser el nuevo estado en la base de datos
        if ($accion == 'confirmar') {
            $estado_nuevo = 'Confirmado';
        } elseif ($accion == 'cancelar') {
            $estado_nuevo = 'Cancelado';
        } else {
            // Si mandan cualquier otra palabra rara, los devolvemos al panel
            header("Location: ../html/reservas.php");
            exit();
        }

        // 4. Hacemos el UPDATE en la tabla reservas
        $sql = "UPDATE reservas SET estado = :estado WHERE idreservas = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':estado' => $estado_nuevo, ':id' => $id_reserva]);

        // 5. Todo salió perfecto, volvemos a la pantalla de reservas
        header("Location: ../html/reservas.php?mensaje=actualizado");
        exit();

    } catch (PDOException $e) {
        die("Error al procesar el cambio de estado: " . $e->getMessage());
    }
} else {
    // Si entran al archivo directo sin hacer clic en un botón
    header("Location: ../html/reservas.php");
    exit();
}
?>
