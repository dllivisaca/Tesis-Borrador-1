<?php
include("../conexion_db.php");

if (isset($_POST['fecha']) && isset($_POST['docid'])) {
    $fecha = $_POST['fecha'];
    $docid = $_POST['docid'];

    // Escapar variables para evitar problemas de seguridad
    $fecha = $database->real_escape_string($fecha);
    $docid = (int)$docid;

    // Obtener el día de la semana (1 para lunes, 7 para domingo)
    $dia_semana = date('w', strtotime($fecha)); // 'w' devuelve 0 (domingo) a 6 (sábado)

    // Consulta para obtener los horarios disponibles en la fecha dada para el doctor
    $sql = "SELECT horainicioman, horafinman, horainiciotar, horafintar 
            FROM disponibilidad_doctor 
            WHERE docid = ? AND dia_semana = ?";

    $stmt = $database->prepare($sql);
    $stmt->bind_param("ii", $docid, $dia_semana);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $disponibilidad = $result->fetch_assoc();
        $horarios = [];

        // Generar intervalos de 30 minutos para mañana y tarde
        function generarIntervalos($inicio, $fin) {
            $intervalos = [];
            $hora_actual = strtotime($inicio);
            $hora_fin = strtotime($fin);

            while ($hora_actual < $hora_fin) {
                $hora_siguiente = strtotime('+30 minutes', $hora_actual);
                if ($hora_siguiente <= $hora_fin) {
                    $intervalos[] = date('H:i', $hora_actual) . ' - ' . date('H:i', $hora_siguiente);
                }
                $hora_actual = $hora_siguiente;
            }

            return $intervalos;
        }

        // Generar los intervalos de disponibilidad del doctor
        $horarios = array_merge(
            generarIntervalos($disponibilidad['horainicioman'], $disponibilidad['horafinman']),
            generarIntervalos($disponibilidad['horainiciotar'], $disponibilidad['horafintar'])
        );

        // Excluir los horarios ya ocupados por otras citas
        $ocupados = [];
        if (isset($_POST['citaid'])) {
            $citaid = (int)$_POST['citaid'];
            $ocupadosQuery = "SELECT hora_inicio FROM citas WHERE docid = ? AND fecha = ? AND citaid != ?";
            $stmt_ocupados = $database->prepare($ocupadosQuery);
            $stmt_ocupados->bind_param("isi", $docid, $fecha, $citaid);
        } else {
            $ocupadosQuery = "SELECT hora_inicio FROM citas WHERE docid = ? AND fecha = ?";
            $stmt_ocupados = $database->prepare($ocupadosQuery);
            $stmt_ocupados->bind_param("is", $docid, $fecha);
        }

        $stmt_ocupados->execute();
        $ocupadosResult = $stmt_ocupados->get_result();
        while ($ocupado = $ocupadosResult->fetch_assoc()) {
            $ocupados[] = substr($ocupado['hora_inicio'], 0, 5);
        }

        // Generar las opciones de horas disponibles para el select
        $options = "";
        foreach ($horarios as $horario) {
            if (!in_array(substr($horario, 0, 5), $ocupados)) {
                $options .= "<option value='" . $horario . "'>" . $horario . "</option>\n";
            }
        }

        echo $options;
    } else {
        echo '<option value="" disabled>No se encontraron horarios disponibles para este día.</option>';
    }
} else {
    echo "Error: No se recibieron los parámetros requeridos.";
}
?>
