<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include("../conexion_db.php");

    $pacid = $_POST['pacid'];
    $docid = $_POST['docid'];
    $especialidad_id = $_POST['especialidad_id'];
    $fecha = $_POST['fecha'];
    $hora_inicio = $_POST['hora_inicio'];
    $estado = 'pendiente';

    // Extraer hora de inicio y calcular la hora de fin (+30 minutos)
    $hora_inicio_obj = new DateTime($hora_inicio);
    $hora_fin = $hora_inicio_obj->add(new DateInterval('PT30M'))->format('H:i');

    // SQL para insertar los datos de la nueva cita
    $sql = "INSERT INTO citas (pacid, docid, especialidad_id, fecha, hora_inicio, hora_fin, estado)
            VALUES ('$pacid', '$docid', '$especialidad_id', '$fecha', '$hora_inicio', '$hora_fin', '$estado')";

    if ($database->query($sql) === TRUE) {
        echo "Cita agendada con éxito";
    } else {
        echo "Error: " . $sql . "<br>" . $database->error;
    }

    $database->close();
}
?>
