<?php
date_default_timezone_set('America/Guayaquil');

ini_set('display_errors', 1); // Muestra errores en el navegador
ini_set('display_startup_errors', 1); // Muestra errores que ocurren al arrancar PHP
error_reporting(E_ALL); // Reporta todos los errores (advertencias, errores, etc.)

require __DIR__ . '/../vendor/autoload.php'; // Asegúrate de tener Twilio PHP SDK instalado

include("../conexion_db.php");

use Twilio\Rest\Client;

// Tus credenciales de Twilio
$sid = ''; // Reemplaza con tu Account SID
$token = ''; // Reemplaza con tu Auth Token

$client = new Client($sid, $token);

// Archivo de log
$log_file = __DIR__ . "/recordatorios_log.txt";

// Determinar si la ejecución es manual o automática
$tipo_ejecucion = (php_sapi_name() === 'cli') ? 'automática' : 'manual';

// Escribir en el log el inicio del proceso con el tipo de ejecución
$fecha_actual = date('Y-m-d H:i:s');
file_put_contents($log_file, "Ejecución $tipo_ejecucion: $fecha_actual\n", FILE_APPEND);


// Escribir en el log el inicio del proceso
/* $fecha_actual = date('Y-m-d H:i:s');
file_put_contents($log_file, "Ejecución automática: $fecha_actual\n", FILE_APPEND); */

// Obtener todas las citas pendientes para verificar los recordatorios
$query = "
    SELECT citas.*, paciente.pactelf, paciente.pacnombre
    FROM citas
    INNER JOIN paciente ON citas.pacid = paciente.pacid
    WHERE (
        (TIMESTAMPDIFF(HOUR, NOW(), STR_TO_DATE(CONCAT(fecha, ' ', hora_inicio), '%Y-%m-%d %H:%i:%s')) BETWEEN 23 AND 25 AND recordatorio_24hrs_enviado = 0)
        OR (TIMESTAMPDIFF(HOUR, NOW(), STR_TO_DATE(CONCAT(fecha, ' ', hora_inicio), '%Y-%m-%d %H:%i:%s')) BETWEEN 2 AND 4 AND recordatorio_3hrs_enviado = 0)
    )
    AND estado = 'pendiente'
";

$result = $database->query($query);


if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $telefono_paciente = $row['pactelf'];
        $nombre_paciente = $row['pacnombre'];
        $fecha_cita = $row['fecha'];
        $hora_inicio = $row['hora_inicio'];
        $cita_id = $row['citaid']; 

        // Obtener el timestamp actual y el de la cita
        $fecha_hora_actual = strtotime(date('Y-m-d H:i:s'));
        $fecha_hora_cita = strtotime("$fecha_cita $hora_inicio");

        // Calcular la diferencia en horas
        $horas_diferencia = ($fecha_hora_cita - $fecha_hora_actual) / 3600;

        // Escribir en el log la diferencia calculada
        file_put_contents($log_file, "Cita ID: $cita_id - Diferencia en horas: $horas_diferencia - Fecha y hora de la cita: $fecha_cita $hora_inicio\n", FILE_APPEND);

        // Verificar si se debe enviar el recordatorio de 24 horas o de 3 horas
        if ($horas_diferencia >= 23.5 && $horas_diferencia <= 24.5 && $row['recordatorio_24hrs_enviado'] == 0) {
            // Mensaje de recordatorio de 24 horas
            $mensaje = "Hola $nombre_paciente, le recordamos que tiene una cita médica programada para el $fecha_cita a las $hora_inicio. Por favor, asegúrese de asistir puntualmente.";

            try {
                // Enviar mensaje de WhatsApp
                $message = $client->messages->create(
                    "whatsapp:$telefono_paciente", // Número del destinatario con 'whatsapp:' como prefijo
                    [
                        'from' => 'whatsapp:+14155238886', // Número de la Sandbox de Twilio
                        'body' => $mensaje
                    ]
                );

                // Actualizar el recordatorio de 24 horas como enviado
                $update_query = "UPDATE citas SET recordatorio_24hrs_enviado = 1 WHERE citaid = $cita_id";
                $database->query($update_query);
                file_put_contents($log_file, "Recordatorio de 24 horas marcado como enviado para la cita ID: $cita_id\n", FILE_APPEND);
            } catch (Exception $e) {
                // Escribir en el log en caso de error
                file_put_contents($log_file, "Error al enviar mensaje a: $nombre_paciente - $telefono_paciente - Error: " . $e->getMessage() . "\n", FILE_APPEND);
            }

        } elseif ($horas_diferencia >= 2.5 && $horas_diferencia <= 3.5 && $row['recordatorio_3hrs_enviado'] == 0) {
            // Mensaje de recordatorio de 3 horas
            $mensaje = "Hola $nombre_paciente, este es un recordatorio de su cita médica que tiene en aproximadamente 3 horas, programada para el $fecha_cita a las $hora_inicio. Asegúrese de llegar puntualmente.";

            try {
                // Enviar mensaje de WhatsApp
                $message = $client->messages->create(
                    "whatsapp:$telefono_paciente", // Número del destinatario con 'whatsapp:' como prefijo
                    [
                        'from' => 'whatsapp:+14155238886', // Número de la Sandbox de Twilio
                        'body' => $mensaje
                    ]
                );

                // Actualizar el recordatorio de 3 horas como enviado
                $update_query = "UPDATE citas SET recordatorio_3hrs_enviado = 1 WHERE citaid = $cita_id";
                $database->query($update_query);
                file_put_contents($log_file, "Recordatorio de 3 horas marcado como enviado para la cita ID: $cita_id\n", FILE_APPEND);
            } catch (Exception $e) {
                // Escribir en el log en caso de error
                file_put_contents($log_file, "Error al enviar mensaje a: $nombre_paciente - $telefono_paciente - Error: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }
    }
} else {
    // Escribir en el log que no hay citas próximas para enviar recordatorios
    file_put_contents($log_file, "No se encontraron citas pendientes.\n", FILE_APPEND);
}
?>