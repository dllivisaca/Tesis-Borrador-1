<?php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error_log.txt');

date_default_timezone_set('America/Guayaquil');

// Definir el archivo de log al inicio
$log_file = __DIR__ . "/webhook_debug_log.txt";

// Agregar la visualización de errores (solo para pruebas)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Crear un log para depuración
file_put_contents($log_file, "El archivo webhook_encuestas.php se está ejecutando.\n", FILE_APPEND);

// Incluir conexión a la base de datos
include("../conexion_db.php");

// Verificar la conexión a la base de datos
if ($database->connect_error) {
    file_put_contents($log_file, "Error de conexión a la base de datos: " . $database->connect_error . "\n", FILE_APPEND);
    die("Connection failed: " . $database->connect_error);
} else {
    file_put_contents($log_file, "Conexión a la base de datos exitosa.\n", FILE_APPEND);
}

// Definir la función de normalización (igual que en citas.php)
function normalizePhoneNumber($phoneNumber) {
    // Eliminar espacios, guiones, paréntesis y signos '+'
    $phoneNumber = preg_replace('/[\s\-()+]/', '', $phoneNumber);

    // Si el número comienza con '0', eliminarlo
    if (substr($phoneNumber, 0, 1) === '0') {
        $phoneNumber = substr($phoneNumber, 1);
    }

    // Si el número no comienza con '593', agregarlo
    if (substr($phoneNumber, 0, 3) !== '593') {
        $phoneNumber = '593' . $phoneNumber;
    }

    // Agregar el signo '+' al inicio
    $phoneNumber = '+' . $phoneNumber;

    return $phoneNumber;
}

// Validar si Twilio envía datos como form-data
if (isset($_POST['Body']) && isset($_POST['From'])) {
    file_put_contents($log_file, "Datos POST recibidos correctamente.\n", FILE_APPEND);

    // Obtener la respuesta y el número del cliente
    $respuesta = trim($_POST['Body']); // Mensaje enviado por el cliente
    $numero_cliente_raw = $_POST['From']; // Este incluye 'whatsapp:+593...'

    // Registrar en el log
    $log_message = "Respuesta recibida: $respuesta\n";
    $log_message .= "Número del cliente recibido: $numero_cliente_raw\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    // Normalizar el número de teléfono
    $numero_cliente = str_replace('whatsapp:', '', $numero_cliente_raw);
    $numero_cliente = normalizePhoneNumber($numero_cliente);

    // Registrar en el log
    $log_message = "Número del cliente después de normalizar: $numero_cliente\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    // Verificar si el cliente ya está en la base de datos
    $query = $database->prepare("SELECT id, estado FROM respuestas_encuestas WHERE numero_cliente = ? ORDER BY id DESC LIMIT 1");
    if (!$query) {
        $log_message = "Error al preparar la consulta: " . $database->error . "\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
    } else {
        $query->bind_param("s", $numero_cliente);
        $query->execute();
        $result = $query->get_result();

        $log_message = "Consulta ejecutada: SELECT id, estado FROM respuestas_encuestas WHERE numero_cliente = '$numero_cliente'\n";
        $log_message .= "Número de filas devueltas: " . $result->num_rows . "\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }

    // Establecer el encabezado para respuesta TwiML
    header("Content-Type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<Response>\n";

    if ($result->num_rows > 0) {
        // Existe un registro para este cliente
        $registro = $result->fetch_assoc();
        $id = $registro['id'];
        $estado = $registro['estado'];

        $log_message = "Registro encontrado: ID=$id, Estado=$estado\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);

        if ($estado == 'esperando_calificacion') {
            $log_message = "El estado es 'esperando_calificacion'.\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);

            // Validar si la respuesta es una calificación válida (1-5)
            if (preg_match('/^[1-5]$/', $respuesta)) {
                $log_message = "La respuesta es una calificación válida: $respuesta\n";
                file_put_contents($log_file, $log_message, FILE_APPEND);

                // Guardar la calificación y actualizar el estado
                $fechaRespuesta = date('Y-m-d H:i:s');
                $update = $database->prepare("UPDATE respuestas_encuestas SET calificacion = ?, estado = 'esperando_comentario', fecha_respuesta = ? WHERE id = ?");
                if (!$update) {
                    $log_message = "Error al preparar la consulta de actualización: " . $database->error . "\n";
                    file_put_contents($log_file, $log_message, FILE_APPEND);
                } else {
                    $update->bind_param("isi", $respuesta, $fechaRespuesta, $id);

                    // Agregar registro al log para depuración
                    $log_message = "Intentando actualizar calificacion: ID=$id, Calificacion=$respuesta, FechaRespuesta=$fechaRespuesta\n";
                    file_put_contents($log_file, $log_message, FILE_APPEND);

                    if($update->execute()){
                        $log_message = "Calificación actualizada exitosamente.\n";
                        file_put_contents($log_file, $log_message, FILE_APPEND);

                        // Enviar el mensaje para el comentario
                        echo "<Message>Gracias por su calificación. Por favor, comparta cualquier comentario o sugerencia para mejorar nuestro servicio</Message>\n";
                    } else {
                        // Registrar el error
                        $error = $update->error;
                        $log_message = "Error al actualizar calificacion: " . $error . "\n";
                        file_put_contents($log_file, $log_message, FILE_APPEND);
                        echo "<Message>Ocurrió un error al guardar su calificación. Por favor, intente de nuevo más tarde.</Message>\n";
                    }
                }
            } else {
                // Respuesta inválida para calificación
                $log_message = "La respuesta no es una calificación válida.\n";
                file_put_contents($log_file, $log_message, FILE_APPEND);
                echo "<Message>Por favor, ingrese una calificación válida (1-5).</Message>\n";
            }
        } elseif ($estado == 'esperando_comentario') {
            $log_message = "El estado es 'esperando_comentario'.\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);

            // Validar el comentario (por ejemplo, longitud mínima de 5 caracteres)
            if (strlen($respuesta) >= 5) {
                $log_message = "El comentario es válido.\n";
                file_put_contents($log_file, $log_message, FILE_APPEND);

                // Guardar el comentario y marcar como completado
                $update = $database->prepare("UPDATE respuestas_encuestas SET comentario = ?, estado = 'completado' WHERE id = ?");
                if (!$update) {
                    $log_message = "Error al preparar la consulta de actualización: " . $database->error . "\n";
                    file_put_contents($log_file, $log_message, FILE_APPEND);
                } else {
                    $update->bind_param("si", $respuesta, $id);

                    // Agregar registro al log para depuración
                    $log_message = "Intentando actualizar comentario: ID=$id, Comentario=$respuesta\n";
                    file_put_contents($log_file, $log_message, FILE_APPEND);

                    if($update->execute()){
                        $log_message = "Comentario actualizado exitosamente.\n";
                        file_put_contents($log_file, $log_message, FILE_APPEND);

                        echo "<Message>Gracias por su comentario. Valoramos mucho su opinión.</Message>\n";
                    } else {
                        // Registrar el error
                        $error = $update->error;
                        $log_message = "Error al actualizar comentario: " . $error . "\n";
                        file_put_contents($log_file, $log_message, FILE_APPEND);
                        echo "<Message>Ocurrió un error al guardar su comentario. Por favor, intente de nuevo más tarde.</Message>\n";
                    }
                }
            } else {
                // Comentario demasiado corto
                $log_message = "El comentario es demasiado corto.\n";
                file_put_contents($log_file, $log_message, FILE_APPEND);
                echo "<Message>Por favor, proporcione un comentario más detallado.</Message>\n";
            }
        } else {
            // El flujo ya fue completado
            $log_message = "El flujo ya fue completado previamente.\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);
            echo "<Message>Gracias, ya hemos registrado su calificación y comentario.</Message>\n";
        }
    } else {
        // No hay registro para este cliente
        $log_message = "No se encontró registro para el número: $numero_cliente\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);

        echo "<Message>No encontramos una encuesta activa para su número.</Message>\n";
    }

    echo "</Response>";
} else {
    // Datos inválidos recibidos
    header("Content-Type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<Response>\n";
    echo "<Message>Datos inválidos recibidos.</Message>\n";
    echo "</Response>";

    $log_message = "Datos inválidos recibidos.\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}
?>
