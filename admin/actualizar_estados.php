<?php
date_default_timezone_set('America/Guayaquil');

// Conexión a la base de datos
include("../conexion_db.php");

if (!$database) {
    die("Error en la conexión a la base de datos: " . mysqli_connect_error());
}

try {

    // Determinar el origen de la ejecución
    $origen = php_sapi_name() === 'cli' ? 'Ejecución automática' : 'Ejecución manual';

    // Obtener la fecha y hora actual
    $fecha_actual = new DateTime();

    // Preparar consulta para actualizar las citas pendientes
    $sql = "UPDATE citas 
            SET estado = 'no atendida' 
            WHERE estado = 'pendiente' 
            AND TIMESTAMPDIFF(HOUR, CONCAT(fecha, ' ', hora_inicio), NOW()) > 24";

    $result = $database->query($sql);

    // Verificar el número de registros afectados
    $registros_afectados = $database->affected_rows;

    if ($result && $registros_afectados > 0) {
        $mensaje = "Estados actualizados correctamente. Registros afectados: $registros_afectados.";
    } elseif ($result) {
        $mensaje = "No se realizaron cambios. No hay citas pendientes que cumplan los criterios.";
    } else {
        $mensaje = "Error al actualizar estados: " . $database->error;
    }

    echo $mensaje;

    // Registrar en el log
    $log_file = __DIR__ . "/actualizar_estados_log.txt";
    $log_message = date('Y-m-d H:i:s') . " - [$origen] $mensaje\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();

    // Registrar el error en el log
    $log_file = __DIR__ . "/actualizar_estados_log.txt";
    $log_message = date('Y-m-d H:i:s') . " - [$origen] Error: " . $e->getMessage() . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

?>
