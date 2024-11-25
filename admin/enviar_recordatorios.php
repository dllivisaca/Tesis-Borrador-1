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

// Obtener citas de las próximas 24 horas y 3 horas antes
$hoy = date('Y-m-d'); // Fecha de hoy
$ahora = date('H:i:s'); // Hora actual
$dentro_de_24_horas = date("Y-m-d H:i:s", strtotime('+24 hours'));
$dentro_de_3_horas = date("Y-m-d H:i:s", strtotime('+3 hours'));

// Consulta para obtener citas en las próximas 24 horas y 3 horas
$query = "SELECT citas.*, paciente.pactelf, paciente.pacnombre 
          FROM citas 
          INNER JOIN paciente ON citas.pacid = paciente.pacid 
          WHERE (
                    (fecha >= '$hoy' AND hora_inicio >= '$ahora' AND fecha <= '$dentro_de_24_horas') 
                    OR 
                    (fecha >= '$hoy' AND hora_inicio <= '$dentro_de_3_horas')
                )
                AND recordatorio_enviado = 0";

$result = $database->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $telefono_paciente = $row['pactelf'];
        $nombre_paciente = $row['pacnombre'];
        $fecha_cita = $row['fecha'];
        $hora_inicio = $row['hora_inicio'];
        $cita_id = $row['citaid']; // Cambiado 'id' por 'citaid'

        // Enviar mensaje de WhatsApp
        $mensaje = "Hola $nombre_paciente, este es un recordatorio de su cita médica programada para el $fecha_cita a las $hora_inicio. Por favor, asegúrese de asistir puntualmente.";

        // Escribir en el log que se intenta enviar un recordatorio
        file_put_contents($log_file, "Intentando enviar mensaje a: $nombre_paciente - $telefono_paciente - Fecha: $fecha_cita Hora: $hora_inicio\n", FILE_APPEND);

        try {
            // Enviar mensaje de WhatsApp
            $client->messages->create(
                "whatsapp:$telefono_paciente", // Número del destinatario con 'whatsapp:' como prefijo
                [
                    'from' => 'whatsapp:+14155238886', // Número de la Sandbox de Twilio
                    'body' => $mensaje
                ]
            );

            // Escribir en el log que se envió un mensaje correctamente
            file_put_contents($log_file, "Mensaje enviado a: $nombre_paciente - $telefono_paciente - Fecha: $fecha_cita Hora: $hora_inicio\n", FILE_APPEND);

            // Marcar el recordatorio como enviado
            $update_query = "UPDATE citas SET recordatorio_enviado = 1 WHERE citaid = $cita_id";
            $database->query($update_query);
        } catch (Exception $e) {
            // Escribir en el log en caso de error
            file_put_contents($log_file, "Error al enviar mensaje a: $nombre_paciente - $telefono_paciente - Error: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
} else {
    // Escribir en el log que no hay citas próximas para enviar recordatorios
    file_put_contents($log_file, "No hay citas próximas para enviar recordatorios.\n", FILE_APPEND);
}
?>
