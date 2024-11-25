<?php
ini_set('display_errors', 1); // Muestra errores en el navegador
ini_set('display_startup_errors', 1); // Muestra errores que ocurren al arrancar PHP
error_reporting(E_ALL); // Reporta todos los errores (advertencias, errores, etc.)

require __DIR__ . '/../vendor/autoload.php';// Asegúrate de tener Twilio PHP SDK instalado

include("../conexion_db.php");

use Twilio\Rest\Client;

// Tus credenciales de Twilio



$sid = ''; // Reemplaza con tu Account SID
$token = ''; // Reemplaza con tu Auth Token

$client = new Client($sid, $token);

// Obtener citas de las próximas 24 horas y 3 horas antes
$hoy = date('Y-m-d'); // Fecha de hoy
$ahora = date('H:i:s'); // Hora actual
$dentro_de_24_horas = date("Y-m-d H:i:s", strtotime('+24 hours'));
$dentro_de_3_horas = date("Y-m-d H:i:s", strtotime('+3 hours'));

// Consulta para obtener citas en las próximas 24 horas y 3 horas
$query = "SELECT citas.*, paciente.pactelf, paciente.pacnombre 
          FROM citas 
          INNER JOIN paciente ON citas.pacid = paciente.pacid 
          WHERE (fecha = '$hoy' AND hora_inicio >= '$ahora' AND fecha <= '$dentro_de_24_horas') 
          OR (fecha = '$hoy' AND hora_inicio <= '$dentro_de_3_horas')";

$result = $database->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $telefono_paciente = $row['pactelf'];
        $nombre_paciente = $row['pacnombre'];
        $fecha_cita = $row['fecha'];
        $hora_inicio = $row['hora_inicio'];

        // Enviar mensaje de WhatsApp
        $mensaje = "Hola $nombre_paciente, este es un recordatorio de su cita médica programada para el $fecha_cita a las $hora_inicio. Por favor, asegúrese de asistir puntualmente.";

        $client->messages->create(
            "whatsapp:$telefono_paciente", // Número del destinatario con 'whatsapp:' como prefijo
            [
                'from' => 'whatsapp:+14155238886', // Número de la Sandbox de Twilio
                'body' => $mensaje
            ]
        );

        echo "Mensaje enviado a $nombre_paciente con número: $telefono_paciente <br>";
    }
} else {
    echo "No hay citas próximas para enviar recordatorios.";
}
?>
