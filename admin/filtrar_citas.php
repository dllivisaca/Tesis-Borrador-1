<?php
include("../conexion_db.php");
header("Content-Type: application/json");

// Obtener los datos enviados desde JavaScript
$data = json_decode(file_get_contents("php://input"), true);

$year = $data['year'] ?? '';
$month = $data['month'] ?? '';
$day = $data['day'] ?? '';

// Construir cláusula WHERE dinámica
$whereClauses = [];
if ($year) {
    $whereClauses[] = "YEAR(fecha) = $year";
}
if ($month) {
    $whereClauses[] = "MONTH(fecha) = $month";
}
if ($day) {
    $whereClauses[] = "DAY(fecha) = $day";
}

$where = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Consultar datos para "Número de citas por especialidad"
$citasPorEspecialidadQuery = "SELECT especialidades.espnombre AS label, COUNT(*) AS value
                              FROM citas
                              INNER JOIN doctor ON citas.docid = doctor.docid
                              INNER JOIN especialidades ON doctor.especialidades = especialidades.id
                              $where
                              GROUP BY especialidades.espnombre";
$citasPorEspecialidadResult = $database->query($citasPorEspecialidadQuery);
$citasPorEspecialidad = [];
while ($row = $citasPorEspecialidadResult->fetch_assoc()) {
    $citasPorEspecialidad['labels'][] = $row['label'];
    $citasPorEspecialidad['data'][] = $row['value'];
}

// Consultar datos para "Número de citas por doctor"
$citasPorDoctorQuery = "SELECT doctor.docnombre AS label, COUNT(*) AS value
                        FROM citas
                        INNER JOIN doctor ON citas.docid = doctor.docid
                        $where
                        GROUP BY doctor.docnombre";
$citasPorDoctorResult = $database->query($citasPorDoctorQuery);
$citasPorDoctor = [];
while ($row = $citasPorDoctorResult->fetch_assoc()) {
    $citasPorDoctor['labels'][] = $row['label'];
    $citasPorDoctor['data'][] = $row['value'];
}

// Consultar datos para "Top 3 horarios con mayor actividad"
$horariosConMayorActividadQuery = "SELECT CONCAT(TIME_FORMAT(hora_inicio, '%H:%i'), ' - ', TIME_FORMAT(hora_fin, '%H:%i')) AS label, COUNT(*) AS value
                                   FROM citas
                                   $where
                                   GROUP BY hora_inicio, hora_fin
                                   ORDER BY value DESC
                                   LIMIT 3";
$horariosConMayorActividadResult = $database->query($horariosConMayorActividadQuery);
$horariosConMayorActividad = [];
while ($row = $horariosConMayorActividadResult->fetch_assoc()) {
    $horariosConMayorActividad['labels'][] = $row['label'];
    $horariosConMayorActividad['data'][] = $row['value'];
}

// Consultar datos para "Top 2 días con mayor actividad"
$diasConMayorActividadQuery = "SELECT DATE_FORMAT(fecha, '%W') AS label, COUNT(*) AS value
                               FROM citas
                               $where
                               GROUP BY label
                               ORDER BY value DESC
                               LIMIT 2";
$diasConMayorActividadResult = $database->query($diasConMayorActividadQuery);
$diasConMayorActividad = [];
while ($row = $diasConMayorActividadResult->fetch_assoc()) {
    $diasConMayorActividad['labels'][] = $row['label'];
    $diasConMayorActividad['data'][] = $row['value'];
}

// Traducción de días al español
$diasEnEspanol = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
];
foreach ($diasConMayorActividad['labels'] as &$label) {
    $label = $diasEnEspanol[$label] ?? $label;
}

// Devolver los datos en formato JSON
echo json_encode([
    "success" => true,
    "citasPorEspecialidad" => $citasPorEspecialidad,
    "citasPorDoctor" => $citasPorDoctor,
    "horariosConMayorActividad" => $horariosConMayorActividad,
    "diasConMayorActividad" => $diasConMayorActividad
]);
?>
