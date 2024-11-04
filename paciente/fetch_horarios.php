<?php
include("../conexion_db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha = $_POST['fecha'];
    $docnombre = $_POST['docnombre'];

    // Obtener el ID del doctor basado en su nombre
    $sql_doctor = "SELECT docid FROM doctor WHERE docnombre = ?";
    $stmt = $database->prepare($sql_doctor);
    $stmt->bind_param("s", $docnombre);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();
    $docid = $doctor['docid'];

    // Obtener el día de la semana seleccionado
    $dia_semana = date('N', strtotime($fecha)); // Obtener el día de la semana (1 para lunes, 7 para domingo)
    $dias = ["1" => "Lunes", "2" => "Martes", "3" => "Miércoles", "4" => "Jueves", "5" => "Viernes", "6" => "Sábado", "7" => "Domingo"];
    $dia_nombre = $dias[$dia_semana];

    // Obtener los horarios disponibles para el doctor en el día seleccionado
    $sql_horarios = "SELECT horainicioman, horafinman, horainiciotar, horafintar 
                     FROM disponibilidad_doctor 
                     WHERE docid = ? AND dia_semana = ?";
    $stmt = $database->prepare($sql_horarios);
    $stmt->bind_param("ss", $docid, $dia_nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $options = "";
    while ($row = $result->fetch_assoc()) {
        // Generar intervalos de 30 minutos para la mañana
        if (!empty($row['horainicioman']) && !empty($row['horafinman'])) {
            $start_time = new DateTime($row['horainicioman']);
            $end_time = new DateTime($row['horafinman']);

            while ($start_time < $end_time) {
                $interval_end = clone $start_time;
                $interval_end->modify('+30 minutes');

                if ($interval_end > $end_time) {
                    $interval_end = $end_time;
                }

                $options .= "<option value='" . $start_time->format('H:i') . " - " . $interval_end->format('H:i') . "'>" . $start_time->format('H:i') . " - " . $interval_end->format('H:i') . "</option>";
                $start_time->modify('+30 minutes');
            }
        }

        // Generar intervalos de 30 minutos para la tarde
        if (!empty($row['horainiciotar']) && !empty($row['horafintar'])) {
            $start_time = new DateTime($row['horainiciotar']);
            $end_time = new DateTime($row['horafintar']);

            while ($start_time < $end_time) {
                $interval_end = clone $start_time;
                $interval_end->modify('+30 minutes');

                if ($interval_end > $end_time) {
                    $interval_end = $end_time;
                }

                $options .= "<option value='" . $start_time->format('H:i') . " - " . $interval_end->format('H:i') . "'>" . $start_time->format('H:i') . " - " . $interval_end->format('H:i') . "</option>";
                $start_time->modify('+30 minutes');
            }
        }
    }

    if ($options == "") {
        $options = "<option value='' disabled>No hay horarios disponibles para la fecha seleccionada</option>";
    }

    echo $options;
}
?>