<?php
// agendar_cita.php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include("../conexion_db.php");

    // Obtener y sanitizar los datos recibidos
    $pacid = isset($_POST['pacid']) ? intval($_POST['pacid']) : 0;
    $docid = isset($_POST['docid']) ? intval($_POST['docid']) : 0;
    $especialidad_id = isset($_POST['especialidad_id']) ? intval($_POST['especialidad_id']) : 0;
    $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
    $horas = isset($_POST['hora_inicio']) ? $_POST['hora_inicio'] : '';
    $estado = 'pendiente';

    // Validar que los campos requeridos no estén vacíos
    if (empty($pacid) || empty($docid) || empty($fecha) || empty($horas)) {
        echo "Error: Faltan campos requeridos.";
        exit;
    }

    // Separar la hora de inicio y la hora de fin
    if (strpos($horas, ' - ') !== false) {
        list($hora_inicio, $hora_fin) = explode(' - ', $horas);
    } else {
        echo "Error: Formato de hora inválido.";
        exit;
    }

    // Preparar la consulta para evitar inyección SQL
    $stmt = $database->prepare("INSERT INTO citas (pacid, docid, especialidad_id, fecha, hora_inicio, hora_fin, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        echo "Error: No se pudo preparar la consulta.";
        exit;
    }

    $stmt->bind_param("iisssss", $pacid, $docid, $especialidad_id, $fecha, $hora_inicio, $hora_fin, $estado);

    if ($stmt->execute()) {
        echo "Cita agendada exitosamente.";
    } else {
        echo "Error al agendar la cita: " . $stmt->error;
    }

    $stmt->close();
    $database->close();
} else {
    echo "Método de solicitud no permitido.";
}
?>
