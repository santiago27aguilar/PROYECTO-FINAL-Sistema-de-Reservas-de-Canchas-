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
        // INICIAMOS LA TRANSACCIÓN: Todo o nada.
        $conexion->beginTransaction(); 

        // 3. Definimos cuál va a ser el nuevo estado en la BD, el método de pago y el DETALLE
        if ($accion == 'confirmar') {
            $estado_nuevo = 'Confirmado';
            $metodo = 'Transferencia'; // Usualmente la seña es por transferencia
            $detalle_pago = 'Seña'; // <-- ACÁ DEFINIMOS QUE ES UNA SEÑA
        } elseif ($accion == 'cancelar') {
            $estado_nuevo = 'Cancelado';
        } elseif ($accion == 'pagado') {
            $estado_nuevo = 'Pagado';
            $metodo = 'Efectivo'; // Usualmente el saldo en el local es en efectivo
            $detalle_pago = 'Monto Restante'; // <-- ACÁ DEFINIMOS QUE ES EL SALDO
        } else {
            // Si mandan cualquier otra palabra rara, los devolvemos al panel
            header("Location: ../html/reservas.php");
            exit();
        }

        // 4. Hacemos el UPDATE en la tabla reservas
        $sql = "UPDATE reservas SET estado = :estado WHERE idreservas = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':estado' => $estado_nuevo, ':id' => $id_reserva]);

        // 5. Si la acción suma dinero (Confirmar o Pagado), hacemos el INSERT en pagos
        if ($accion == 'confirmar' || $accion == 'pagado') {
            
            // Agregamos detalle_senia a la consulta SQL
            $sql_pago = "INSERT INTO pagos (monto, metodo_pago, detalle_senia, fecha_pago, usuario_idusuario, reservas_idreservas) 
                         VALUES (:monto, :metodo, :detalle, NOW(), :id_usuario, :id_reserva)";
            
            $stmt_pago = $conexion->prepare($sql_pago);
            
            // Monto fijo como ejemplo (podrías dinamizarlo después para que lea el precio de la cancha)
            $monto_pago = 5000; 
            
            // Capturamos el ID del usuario/admin que está cobrando. 
            $id_admin = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 1; 

            // Pasamos el valor del detalle al momento de guardar
            $stmt_pago->execute([
                ':monto' => $monto_pago,
                ':metodo' => $metodo,
                ':detalle' => $detalle_pago, // <-- AGREGADO AL EXECUTE
                ':id_usuario' => $id_admin,
                ':id_reserva' => $id_reserva
            ]);
        }

        // 6. ¡Todo salió perfecto! Guardamos ambos cambios juntos (COMMIT)
        $conexion->commit(); 

        // Volvemos a la pantalla de reservas
        header("Location: ../html/reservas.php?mensaje=actualizado");
        exit();

    } catch (PDOException $e) {
        // SI ALGO FALLA, DESHACEMOS TODO (ROLLBACK) para no dejar datos a medias
        if ($conexion->inTransaction()) {
            $conexion->rollBack(); 
        }
        die("Error al procesar el cambio de estado: " . $e->getMessage());
    }
} else {
    // Si entran al archivo directo sin hacer clic en un botón
    header("Location: ../html/reservas.php");
    exit();
}
?>
