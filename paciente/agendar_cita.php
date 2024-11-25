<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include("../conexion_db.php");

    $pacid = $_POST['pacid'];
    $docid = $_POST['docid'];
    $especialidad_id = $_POST['especialidad_id'];
    $fecha = $_POST['fecha'];
    $horas = $_POST['hora_inicio']; // Este valor tiene el formato "14:00 - 14:30"
    $estado = 'pendiente';

    // Mostrar valores recibidos para depurar
    /* echo "Paciente ID: " . $pacid . "<br>";
    echo "Doctor ID: " . $docid . "<br>";
    echo "Especialidad ID: " . $especialidad_id . "<br>";
    echo "Fecha: " . $fecha . "<br>";
    echo "Horas: " . $horas . "<br>"; */

    // Separar la hora de inicio y la hora de fin
    list($hora_inicio, $hora_fin) = explode(' - ', $horas);

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