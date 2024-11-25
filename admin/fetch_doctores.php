<?php
include("../conexion_db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["especialidad_id"])) {
        $especialidad_id = intval($_POST["especialidad_id"]);
        
        // Consultar los doctores que pertenezcan a la especialidad seleccionada
        $query = "SELECT docid, docnombre FROM doctor WHERE especialidades = ?";
        $stmt = $database->prepare($query);
        $stmt->bind_param("i", $especialidad_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<option value="">Escoge un doctor de la lista</option>';
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($row['docid'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['docnombre'], ENT_QUOTES, 'UTF-8') . '</option>';
            }
        } else {
            echo '<option value="">No hay doctores disponibles para esta especialidad</option>';
        }
    } else {
        echo '<option value="">Error: Especialidad no seleccionada</option>';
    }
} else {
    echo '<option value="">Error: Solicitud incorrecta</option>';
}
?>
