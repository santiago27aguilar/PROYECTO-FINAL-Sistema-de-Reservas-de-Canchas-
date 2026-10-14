<?php
    session_start();
    // incluimos la conexion.php 
    include 'conexion.php';

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        // captura los datos del formulario
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $dni = trim($_POST['dni']);
        $telefono = trim($_POST['telefono']);
        $correo = trim($_POST['correo']);

        if(empty($nombre) || empty($dni) || empty($telefono)){
            header("Location:../html/inicio.php?error=vacio");
            exit();
        }

        try{

            $checkDni = $conexion->prepare("SELECT idclientes FROM clientes WHERE dni = :dni");
            $checkDni -> bindParam(':dni', $dni);
            $checkDni -> execute();

            if($checkDni->rowCount() > 0){
                header("Location:../html/inicio.php?error=duplicado");
                exit();
            }

            // escribimos la consulta sql
            $sql = "INSERT INTO clientes (nombre, apellido, dni, telefono, correo) VALUES (:nom, :ape, :dni, :tel, :cor)";

            if ($stmt->execute()) {
                // Si se guardó bien, mandamos el mensaje "registrado"
                header("Location: ../html/inicio.php?mensaje=registrado");
                exit();
            } else {
                // Si hubo un error (ej: DNI duplicado)
                header("Location: ../html/inicio.php?error=registro_fallido");
                exit();
            } 

            $stmt = $conexion->prepare($sql);

            // ejecutamos
            $stmt->execute([':nom' => $nombre, ':ape' => $apellido, ':dni' => $dni, ':tel' => $telefono, ':cor' => $correo]);

            header("Location:../html/inicio.php?exito=1");


        }
        catch(PDOExeption $e){
            echo "Error al guardar datos del cliente cliente: " . $e->getMessage();
        }
    }

?>
