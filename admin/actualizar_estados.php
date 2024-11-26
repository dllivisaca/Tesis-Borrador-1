<?php
date_default_timezone_set('America/Guayaquil');

// Conexión a la base de datos
include("../conexion_db.php");

if (!$database) {
    die("Error en la conexión a la base de datos: " . mysqli_connect_error());
}

try {
    // Obtener la fecha y hora actual
    $fecha_actual = new DateTime();

    // Preparar consulta para actualizar las citas pendientes
    $sql = "UPDATE citas 
            SET estado = 'no atendida' 
            WHERE estado = 'pendiente' 
            AND TIMESTAMPDIFF(HOUR, CONCAT(fecha, ' ', hora_inicio), NOW()) > 24";

    $result = $database->query($sql);

    // Verificar si la consulta se ejecutó correctamente
    if ($result) {
        $rowsAffected = $database->affected_rows; // Número de filas afectadas
        if ($rowsAffected > 0) {
            echo "Estados actualizados correctamente. Registros afectados: $rowsAffected.";
        } else {
            echo "No se realizaron cambios. No hay citas pendientes que cumplan los criterios.";
        }
    } else {
        echo "Error al actualizar estados: " . $database->error;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Registrar en el log
$log_file = __DIR__ . "/actualizar_estados_log.txt";
if (isset($rowsAffected) && $rowsAffected > 0) {
    $log_message = date('Y-m-d H:i:s') . " - Estados actualizados correctamente. Registros afectados: $rowsAffected.\n";
} elseif (isset($rowsAffected) && $rowsAffected == 0) {
    $log_message = date('Y-m-d H:i:s') . " - No se realizaron cambios. No hay citas pendientes que cumplan los criterios.\n";
} else {
    $log_message = date('Y-m-d H:i:s') . " - Error al actualizar estados: " . $database->error . "\n";
}
file_put_contents($log_file, $log_message, FILE_APPEND);

?>
