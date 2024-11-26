<?php
date_default_timezone_set('America/Guayaquil');

// Crear un log para depuración inicial
$log_file = __DIR__ . "/webhook_debug_log.txt";
$log_message = "Datos recibidos: " . print_r($_POST, true) . "\n";
file_put_contents($log_file, $log_message, FILE_APPEND);

// Incluir conexión a la base de datos
include("../conexion_db.php");

// Validar si Twilio envía datos como form-data
if (isset($_POST['Body']) && isset($_POST['From'])) {
    // Obtener la respuesta y el número del cliente
    $respuesta = trim($_POST['Body']); // Mensaje enviado por el cliente
    $numero_cliente = str_replace('whatsapp:', '', $_POST['From']); // Eliminar el prefijo 'whatsapp:'

    // Verificar si el cliente ya está en la base de datos
    $query = $database->prepare("SELECT id, estado FROM respuestas_encuestas WHERE numero_cliente = ? ORDER BY id DESC LIMIT 1");
    $query->bind_param("s", $numero_cliente);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        // Existe un registro para este cliente
        $registro = $result->fetch_assoc();
        $id = $registro['id'];
        $estado = $registro['estado'];

        if ($estado == 'esperando_calificacion') {
            // Validar si la respuesta es una calificación válida (1-5)
            if (preg_match('/^[1-5]$/', $respuesta)) {
                // Guardar la calificación y actualizar el estado
                $update = $database->prepare("UPDATE respuestas_encuestas SET calificacion = ?, estado = 'esperando_comentario' WHERE id = ?");
                $update->bind_param("ii", $respuesta, $id);
                $update->execute();

                // Enviar el mensaje para el comentario
                echo "Por favor, comparta cualquier comentario o sugerencia para mejorar nuestro servicio. Responda este mensaje.";
            } else {
                // Respuesta inválida para calificación
                echo "Por favor, ingrese una calificación válida (1-5).";
            }
        } elseif ($estado == 'esperando_comentario') {
            // Guardar el comentario y marcar como completado
            $update = $database->prepare("UPDATE respuestas_encuestas SET comentario = ?, estado = 'completado', fecha_respuesta = NOW() WHERE id = ?");
            $update->bind_param("si", $respuesta, $id);
            $update->execute();

            echo "Gracias por su comentario. Valoramos mucho su opinión.";
        } else {
            // El flujo ya fue completado
            echo "Gracias, ya hemos registrado su calificación y comentario.";
        }
    } else {
        // No hay registro, iniciar el flujo con la primera pregunta
        $insert = $database->prepare("INSERT INTO respuestas_encuestas (numero_cliente, estado, fecha_envio) VALUES (?, 'esperando_calificacion', NOW())");
        $insert->bind_param("s", $numero_cliente);
        $insert->execute();

        echo "Hola, gracias por visitarnos. ¿Cómo calificaría el servicio recibido hoy?\n1: Muy insatisfecho\n2: Insatisfecho\n3: Neutral\n4: Satisfecho\n5: Muy satisfecho";
    }
} else {
    echo "Datos inválidos recibidos.";
}
?>