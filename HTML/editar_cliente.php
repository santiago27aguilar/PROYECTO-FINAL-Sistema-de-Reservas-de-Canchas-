<?php
    session_start();
    if(!isset($_SESSION['usuario_nombre'])){ header("Location: login.php"); exit(); }
    include '../php/conexion.php';

    if (!isset($_GET['id'])) { header("Location: inicio.php"); exit(); }

    $id = $_GET['id'];
    $stmt = $conexion->prepare("SELECT * FROM clientes WHERE idclientes = :id");
    $stmt->execute([':id' => $id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cliente) {die("Cliente no encontrado."); }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="../css/estilos_editar_cliente.css?v=1">
</head>
<body>
    <div class="container">
        
        <div class="card-blanca">
            <div class="header-editar">
                <h2>EDITAR DATOS DEL CLIENTE</h2>
                <img src="../img/icono-editar.png" alt="Foto Editar" class="img-cuadro">
            </div>

            <form action="../php/actualizar_cliente.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $cliente['idclientes']; ?>">

                <div class="grid-editar-top">
                    
                    <div class="form-group izq">
                        <label>NOMBRE <span class="asterisco">*</span></label>
                        <input type="text" name="nombre" value="<?php echo $cliente['nombre']; ?>" required>
                    </div>
                    <div class="form-group der">
                        <label>DOCUMENTO <span class="asterisco">*</span></label>
                        <input type="number" name="dni" value="<?php echo $cliente['dni']; ?>" required>
                    </div>

                    <div class="form-group izq">
                        <label>APELLIDO <span class="asterisco">*</span></label>
                        <input type="text" name="apellido" value="<?php echo $cliente['apellido']; ?>" required>
                    </div>
                    <div class="form-group der">
                        <label>TELEFONO <span class="asterisco">*</span></label>
                        <input type="text" name="telefono" value="<?php echo $cliente['telefono']; ?>">
                    </div>

                </div>

                <div class="form-group centro correo-container">
                    <label>CORREO ELECTRONICO <span class="asterisco">*</span></label>
                    <input type="email" name="correo" value="<?php echo $cliente['correo']; ?>">
                </div>

                <div class="acciones-container">
                    <button type="submit" class="btn-actualizar">ACTUALIZAR CAMBIOS</button>
                    <a href="inicio.php" class="link-volver">VOLVER ATRAS</a>
                </div>
                
            </form>
        </div>

    </div>
</body>
</html>
