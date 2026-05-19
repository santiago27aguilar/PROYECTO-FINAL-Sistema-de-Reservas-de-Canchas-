<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Datos del Cliente
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dni      = trim($_POST['dni']);
    $correo   = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    
    // 2. Datos de la Reserva
    $id_cancha     = $_POST['idcancha'];
    $fecha         = $_POST['fecha_reserva'];
    $hora_inicio_t = $_POST['hora_inicio']; 
    $duracion      = $_POST['duracion'];    

    $inicio_timestamp = strtotime("$fecha $hora_inicio_t");
    $fin_timestamp    = $inicio_timestamp + ($duracion * 3600);
    $hora_inicio_db   = date('Y-m-d H:i:s', $inicio_timestamp);
    $hora_fin_db      = date('Y-m-d H:i:s', $fin_timestamp);

    try {
        // --- VALIDACIÓN DE DISPONIBILIDAD CORREGIDA ---
        // Buscamos si hay algún turno activo (no cancelado) que se superponga en esa misma cancha
        $sql_dispo = "SELECT COUNT(*) as ocupado 
                      FROM reservas 
                      WHERE cancha_idcancha = :id_can 
                      AND estado != 'Cancelado' 
                      AND (
                          (hora_inicio < :fin AND hora_fin > :inicio)
                      )";
        
        $stmt_dispo = $conexion->prepare($sql_dispo);
        $stmt_dispo->execute([
            ':id_can' => $id_cancha,
            ':inicio' => $hora_inicio_db,
            ':fin'    => $hora_fin_db
        ]);
        
        $resultado = $stmt_dispo->fetch(PDO::FETCH_ASSOC);

        if ($resultado['ocupado'] > 0) {
            header("Location: ../html/cliente.php?error=ocupado");
            exit();
        }

        $conexion->beginTransaction();

        // Aseguramos que exista el usuario 1 para la FK
        $conexion->exec("INSERT IGNORE INTO usuario (idusuario, nombre, password, rol) 
                         VALUES (1, 'Admin Sistema', '1234', 'Admin')");

        // 3. Lógica de Cliente Único
        $sql_check = "SELECT idclientes FROM clientes WHERE dni = :dni LIMIT 1";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->execute([':dni' => $dni]);
        $cliente = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($cliente) {
            $id_cliente = $cliente['idclientes'];
            $stmt_upd = $conexion->prepare("UPDATE clientes SET nombre = :nom, apellido = :ape, correo = :correo, telefono = :tel WHERE idclientes = :id");
            $stmt_upd->execute([':nom' => $nombre, ':ape' => $apellido, ':correo' => $correo, ':tel' => $telefono, ':id' => $id_cliente]);
        } else {
            $sql_ins_cli = "INSERT INTO clientes (nombre, apellido, dni, telefono, correo) 
                             VALUES (:nom, :ape, :dni, :tel, :mail)";
            $stmt_ins_cli = $conexion->prepare($sql_ins_cli);
            $stmt_ins_cli->execute([':nom' => $nombre, ':ape' => $apellido, ':dni' => $dni, ':tel' => $telefono, ':mail' => $correo]);
            $id_cliente = $conexion->lastInsertId();
        }

        // 4. Registro de Reserva (Estado: 'Reservado')
        $sql_reserva = "INSERT INTO reservas (hora_inicio, hora_fin, estado, usuario_idusuario, cancha_idcancha, clientes_idclientes) 
                        VALUES (:inicio, :fin, 'Reservado', 1, :id_can, :id_cli)"; 
        
        $stmt_res = $conexion->prepare($sql_reserva);
        $stmt_res->execute([':inicio' => $hora_inicio_db, ':fin' => $hora_fin_db, ':id_can' => $id_cancha, ':id_cli' => $id_cliente]);

        $conexion->commit();
        
        header("Location: ../html/cliente.php?reserva=ok&nom=" . urlencode($nombre) . "&ape=" . urlencode($apellido));
        exit();

    } catch (Exception $e) {
        $conexion->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
