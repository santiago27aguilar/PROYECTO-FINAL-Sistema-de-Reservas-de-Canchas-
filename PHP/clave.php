<?php
echo "<h3>Claves Encriptadas para copiar y pegar en MySQL Workbench:</h3>";
echo "<b>Clave Admin (1234):</b> " . password_hash("1234", PASSWORD_DEFAULT) . "<br><br>";
echo "<b>Clave Dueño (4321):</b> " . password_hash("4321", PASSWORD_DEFAULT) . "<br>";
?>
