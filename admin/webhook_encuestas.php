<?php
// Crear un log para depuración inicial
$log_file = __DIR__ . "/webhook_debug_log.txt";
$log_message = "Datos recibidos: " . print_r($_POST, true) . "\n";
file_put_contents($log_file, $log_message, FILE_APPEND);

// Incluir conexión a la base de datos
include("../conexion_db.php");

// Validar si Twilio envía datos como form-data
if (isset($_POST['Body']) && isset($_POST['From'])) {
    // Obtener la respuesta y el número del cliente
    $respuesta = $_POST['Body']; // Mensaje enviado por el cliente
    $numero_cliente = str_replace('whatsapp:', '', $_POST['From']); // Eliminar el prefijo 'whatsapp:'

    // Preparar la consulta para insertar los datos en la base
    $stmt = $database->prepare("INSERT INTO respuestas_encuestas (numero_cliente, respuesta) VALUES (?, ?)");
    $stmt->bind_param("ss", $numero_cliente, $respuesta);

    if ($stmt->execute()) {
        // Respuesta de éxito para Twilio
        echo "Respuesta almacenada correctamente.";
    } else {
        // Respuesta de error
        echo "Error al almacenar la respuesta: " . $stmt->error;
    }
    $stmt->close();

    // Log exitoso
    $log_file = __DIR__ . "/encuestas_log.txt";
    $log_message = date('Y-m-d H:i:s') . " - Número: $numero_cliente - Respuesta: $respuesta\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
} else {
    // Respuesta de error si no llegan datos válidos
    echo "Datos inválidos recibidos.";

    // Log de error
    $log_file = __DIR__ . "/webhook_debug_log.txt";
    $log_message = "Error: Datos no válidos recibidos. POST: " . print_r($_POST, true) . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}
?>
