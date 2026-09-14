<?php
// Verificamos que venga por POST y traiga el ID de la reserva
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_reserva'])) {
    include 'conexion.php'; // Tu archivo de conexión a la BD
    
    $id_reserva = $_POST['id_reserva'];

    try {
        // La magia: Hacemos un UPDATE (no DELETE) para que te quede el historial
        $sql = "DELETE FROM reservas WHERE idreservas = :id";
        //$sql = "UPDATE reservas SET estado = 'Cancelado' WHERE idreservas = :id"; 
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id_reserva, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Si todo salió bien, lo mandamos de vuelta a la pantalla de búsqueda con un mensaje de éxito
            header("Location: ../html/mis_reservas.php?mensaje=cancelado");
            exit();
        } else {
            // Si hubo un error raro, lo devolvemos con error
            header("Location: ../html/mis_reservas.php?error=1");
            exit();
        }
        
    } catch (Exception $e) {
        die("Error en la base de datos al cancelar: " . $e->getMessage());
    }
} else {
    // Si entran por URL sin apretar el botón, los pateamos al inicio
    header("Location: ../html/mis_reservas.php");
    exit();
}
?>
