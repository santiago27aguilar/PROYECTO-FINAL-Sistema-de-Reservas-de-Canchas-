<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Cancha - Pampa Fútbol</title>
    <link rel="stylesheet" href="../css/estilos_cliente.css">
</head>
<body>
 
<div class="reserva-card">

    <!-- Mensaje de Éxito -->
    <?php if (isset($_GET['reserva']) && $_GET['reserva'] == 'ok'): ?>
        <?php 
            $num = "5493814152422"; 
            $nom = isset($_GET['nom']) ? $_GET['nom'] : 'Cliente';
            $texto = rawurlencode("¡Hola! Soy $nom. Solicité un turno en Pampa Fútbol y quiero confirmar el pago.");
        ?>
        <div class="mensaje-exito">
            <h3>¡Turno Reservado con Éxito!</h3>
            <p class="estado-pago">ESTADO: PAGO PENDIENTE</p>
            <div>
                <a href="https://api.whatsapp.com/send?phone=<?php echo $num; ?>&text=<?php echo $texto; ?>" target="_blank" class="btn-whatsapp">Contactar por WhatsApp</a>
            </div>
            <p>Si pagás por transferencia, Alias: <span class="alias-destacado">planeta.futbol.padel</span></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] == 'ocupado'): ?>
        <div class="mensaje-error">
            <h3>¡Horario Ocupado!</h3>
            <p>El horario ya se encuentra reservado. Por favor, Elije otra hora u otra cancha.</p>
        </div>
    <?php endif; ?>

    <h2>RESERVA TU TURNO</h2>
    <img src="../img/icono-turno.png" alt="Icono" class="icono-usuario">

    <form action="../php/procesar_reserva.php" method="POST">
        
        <!-- Grilla Principal -->
        <div class="form-main-grid">
            
            <!-- Columna Izquierda -->
            <div class="columna-izq">
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Nombre</label><input type="text" name="nombre" required placeholder="Ej: Juan">
                    </div>
                    <div class="form-group">
                        <label>Documento</label><input type="text" name="dni" placeholder="Sin puntos" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Apellido</label><input type="text" name="apellido" required placeholder="Ej: Perez">
                    </div>
                    <div class="form-group">
                        <label>WhatsApp</label><input type="tel" name="telefono" required placeholder="381......." oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    </div>
                </div>
                <div class="form-group">
                    <label>Correo Electrónico</label><input type="email" name="correo" required placeholder="ejemplo@gmail.com">
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="columna-der">
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Cancha</label>
                        <select name="idcancha" id="id_cancha" required>
                            <option value="">Seleccionar</option>
                            <?php
                                include '../php/conexion.php';
                                $q = $conexion->query("SELECT idcancha, tipo_cancha, precio_hora FROM cancha");
                                while($r = $q->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='".$r['idcancha']."' data-precio='".$r['precio_hora']."'>".$r['tipo_cancha']."</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Fecha</label><input type="date" name="fecha_reserva" id="fecha_reserva" required></div>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Duración</label>
                        <select name="duracion" id="duracion_turno">
                            <option value="1">1 Hora</option>
                            <option value="2">2 Horas</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Hora Inicio</label>
                        <select name="hora_inicio" id="hora_reserva" required>
                            <option value="">Elegir Horario...</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="visibility: hidden;">Precio Oculto</label>
                    <div id="cuadro_precio" class="precio-banner invisible">
                        <p>
                            <span id="texto_duracion">Total:</span> 
                            <strong id="precio_final">$0</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-formulario">
            <div class="gestion-container">
                <a href="../html/mis_reservas.php" class="link-gestion">VER RESERVA - CANCELAR RESERVA</a>
            </div>
            <button type="submit" class="btn-enviar">CONFIRMAR TURNO</button>
        </div>
        
    </form>
</div>

<script src="../js/logica_reserva.js"></script>
</body>
</html>
