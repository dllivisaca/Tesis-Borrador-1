<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include("../conexion_db.php");

    // Mostrar todos los datos recibidos
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';
    

    $pacid = $_POST['pacid'];
    $docid = $_POST['docid'];
    $especialidad_id = $_POST['especialidad_id'];
    $fecha = $_POST['fecha'];
    //$horas = $_POST['hora_inicio'];
    $estado = 'pendiente';

    // Obtenemos las horas
    if (isset($_POST['hora_inicio']) && isset($_POST['hora_fin'])) {
        $hora_inicio = $_POST['hora_inicio'];
        $hora_fin = $_POST['hora_fin'];
    } elseif (isset($_POST['hora_disponible'])) {
        // Si no se enviaron por separado, intentamos obtenerlas de 'hora_disponible'
        $horas = $_POST['hora_disponible'];
        list($hora_inicio, $hora_fin) = explode(' - ', $horas);
    } else {
        echo "Por favor, seleccione una hora válida.";
        exit();
    }

    // Verificar que todos los campos están presentes
    if (!$pacid || !$docid || !$especialidad_id || !$fecha || !$hora_inicio || !$hora_fin) {
        echo "Por favor, complete todos los campos del formulario.";
        exit();
    }

    // Mostrar valores recibidos para depurar
    echo "Paciente ID: " . $pacid . "<br>";
    echo "Doctor ID: " . $docid . "<br>";
    echo "Especialidad ID: " . $especialidad_id . "<br>";
    echo "Fecha: " . $fecha . "<br>";
    echo "Hora inicio: " . $hora_inicio . "<br>";
    echo "Hora fin: " . $hora_fin . "<br>";

    // SQL para insertar los datos de la nueva cita
    $stmt = $database->prepare("INSERT INTO citas (pacid, docid, especialidad_id, fecha, hora_inicio, hora_fin, estado)
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissss", $pacid, $docid, $especialidad_id, $fecha, $hora_inicio, $hora_fin, $estado);

    if ($stmt->execute()) {
        echo "Cita agendada con éxito";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $database->close();

}
?>
