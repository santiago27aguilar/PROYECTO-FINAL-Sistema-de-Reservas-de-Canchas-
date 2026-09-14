<?php
include 'conexion.php';

$id_cancha = $_GET['id_cancha'];
$fecha = $_GET['fecha'];

// 1. Definimos tus horarios de alquiler
$horarios_posibles = ['14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00', '00:00'];

try {
    // 2. Traemos hora_inicio y hora_fin de las reservas de ese día
    $sql = "SELECT DATE_FORMAT(hora_inicio, '%H:%i') as inicio, 
                   DATE_FORMAT(hora_fin, '%H:%i') as fin 
            FROM reservas 
            WHERE cancha_idcancha = ? 
            AND DATE(hora_inicio) = ? 
            AND estado != 'Cancelado'";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_cancha, $fecha]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ocupados = [];

    // 3. Procesamos cada reserva para sacar TODAS las horas que ocupa
    foreach ($reservas as $reserva) {
        $inicio = strtotime($reserva['inicio']);
        $fin = strtotime($reserva['fin']);
        
        // Recorremos desde el inicio hasta el fin, sumando de a 1 hora (3600 segundos)
        for ($hora = $inicio; $hora < $fin; $hora += 3600) {
            $ocupados[] = date('H:i', $hora);
        }
    }

    // 4. Quitamos los ocupados de la lista de posibles
    $disponibles = array_diff($horarios_posibles, $ocupados);

    // 5. Devolvemos la lista limpia al JavaScript
    echo json_encode(array_values($disponibles));

} catch (PDOException $e) {
    echo json_encode([]);
}
?>
