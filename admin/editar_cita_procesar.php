<?php
include("../conexion_db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar que se reciban todos los datos requeridos
    if (isset($_POST['citaid'], $_POST['docid'], $_POST['fecha'], $_POST['hora'])) {
        $citaid = intval($_POST['citaid']);
        $docid = intval($_POST['docid']);
        $fecha = $_POST['fecha'];
        list($hora_inicio, $hora_fin) = explode(' - ', $_POST['hora']);
    
        // Escapar las variables para evitar problemas de seguridad
        $fecha = $database->real_escape_string($fecha);
        $hora_inicio = $database->real_escape_string($hora_inicio);
        $hora_fin = $database->real_escape_string($hora_fin);

        // Mostrar los valores para depurar
        error_log("Datos recibidos - CitaID: $citaid, DocID: $docid, Fecha: $fecha, Hora Inicio: $hora_inicio, Hora Fin: $hora_fin");
    
        // Actualizar la cita en la base de datos
        $sql = "UPDATE citas 
                SET fecha = ?, hora_inicio = ?, hora_fin = ?
                WHERE citaid = ? AND docid = ?";
                    
        $stmt = $database->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssii", $fecha, $hora_inicio, $hora_fin, $citaid, $docid);
    
            if ($stmt->execute()) {
                echo "success";
            } else {
                echo "Error al actualizar la cita. Error de ejecución: " . $stmt->error;
            }
    
            $stmt->close();
        } else {
            echo "Error en la preparación de la consulta: " . $database->error;
        }
    } else {
        echo "Error: Faltan datos requeridos para actualizar la cita.";
    }
} else {
    echo "Error: Solicitud no válida.";
}
?>
