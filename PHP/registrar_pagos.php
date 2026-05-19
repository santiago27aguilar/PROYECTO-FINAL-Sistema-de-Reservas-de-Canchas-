<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../html/login.php");
    exit();
}

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_reserva = $_POST["id_reserva"];
    $monto = $_POST["monto"];
    $metodo = $_POST["metodo_pago"];

    if (empty($monto) || $monto <= 0) {
        header("Location: ../html/pagos.php?error=monto_invalido");
        exit();
    }

    if ($metodo == "TARJETA" && isset($_POST['tipo_tarjeta']) && !empty($_POST['tipo_tarjeta'])) {
        $metodo = "Tarjeta: " . $_POST['tipo_tarjeta'];
    }

    $id_usuario = $_SESSION['id_usuario'];

    try {
        $sql = "INSERT INTO pagos (monto, metodo_pago, fecha_pago, usuario_idusuario, reservas_idreservas) VALUES (:monto, :metodo, NOW(), :user, :reserva)";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt->execute([':monto' => $monto, ':metodo' => $metodo, ':user' => $id_usuario, ':reserva' => $id_reserva])) {
            
            $sql_update = "UPDATE reservas SET estado = 'Pagado' WHERE idreservas = :reserva";
            $stmt_update = $conexion->prepare($sql_update);
            
            if ($stmt_update->execute([':reserva' => $id_reserva])) {

                $sql_cancha = "UPDATE cancha SET estado = 'Libre' WHERE idcancha = (SELECT cancha_idcancha FROM reservas WHERE idreservas = :reserva)";
                $stmt_can = $conexion->prepare($sql_cancha);
                $stmt_can->execute([':reserva' => $id_reserva]);

                header("Location: ../html/pagos.php?exito=1");
                exit();
            }
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} 

else {
    header("Location: ../html/pagos.php");
    exit();
}

?>
