<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Horarios</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
    </style>
</head>
<body>
    <?php
    session_start();

    if (isset($_SESSION["usuario"])) {
        if (($_SESSION["usuario"] == "") || ($_SESSION['usuario_rol'] != 'adm')) {
            header("location: ../login.php");
        }
    } else {
        header("location: ../login.php");
    }

    // Importar la base de datos
    include("../conexion_db.php");

    // Función para generar opciones de horarios
    function generarOpcionesHorario($horaInicio, $horaFin, $valorSeleccionado = '') {
        $minutosIntervalo = 30;
        $horaInicioArr = explode(':', $horaInicio);
        $horaFinArr = explode(':', $horaFin);

        $horaInicial = (int)$horaInicioArr[0];
        $minutoInicial = (int)$horaInicioArr[1];
        $horaFinal = (int)$horaFinArr[0];
        $minutoFinal = (int)$horaFinArr[1];

        for ($hora = $horaInicial; $hora <= $horaFinal; $hora++) {
            for ($minuto = 0; $minuto < 60; $minuto += $minutosIntervalo) {
                if ($hora === $horaFinal && $minuto >= $minutoFinal) break;

                $horaFormateada = sprintf('%02d:%02d', $hora, $minuto);
                $selected = ($horaFormateada === $valorSeleccionado) ? 'selected' : '';
                echo "<option value='$horaFormateada' $selected>$horaFormateada</option>";
            }
        }
    }

    // Función para normalizar nombres de días
    function normalizarNombreDia($nombreDia) {
        $nombreDia = strtolower($nombreDia); // Convertir a minúsculas
        $nombreDia = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'n'],
            $nombreDia
        ); // Remover acentos y eñes
        return $nombreDia;
    }

    // Obtener el doctor y sus horarios guardados
    if ($_GET) {
        $docid = $_GET['id'];

        // Consulta para obtener los datos del doctor
        $sql = "SELECT doctor.docnombre, doctor.especialidades, disponibilidad_doctor.* 
                FROM doctor 
                LEFT JOIN disponibilidad_doctor 
                ON doctor.docid = disponibilidad_doctor.docid 
                WHERE doctor.docid = '$docid'";
        $result = $database->query($sql);
        $doctor = $result->fetch_assoc();

        // Obtener especialidad
        $especialidad_res = $database->query("SELECT espnombre FROM especialidades WHERE id = '{$doctor['especialidades']}'");
        $especialidad = $especialidad_res->fetch_assoc()['espnombre'];

        // Consulta para obtener los horarios guardados del doctor
        $sql_horarios = "SELECT * FROM disponibilidad_doctor WHERE docid = '$docid'";
        $result_horarios = $database->query($sql_horarios);

        $horarios_guardados = [];
        while ($horario = $result_horarios->fetch_assoc()) {
            $dia_semana = normalizarNombreDia($horario['dia_semana']); // Normalizar el nombre del día
            $horarios_guardados[$dia_semana] = [
                'inicio_manana' => substr($horario['horainicioman'], 0, 5),  // Formato "HH:MM"
                'fin_manana' => substr($horario['horafinman'], 0, 5),
                'inicio_tarde' => substr($horario['horainiciotar'], 0, 5),
                'fin_tarde' => substr($horario['horafintar'], 0, 5)
            ];
        }
    }

    ?>
    <div class="container">
        <h2>Editar Horario del Doctor</h2>
        <form action="update_horario.php" method="POST">
            <input type="hidden" name="docid" value="<?php echo $docid; ?>">

            <label for="docnombre">Nombre del Doctor:</label>
            <input type="text" id="docnombre" name="docnombre" value="<?php echo $doctor['docnombre']; ?>" readonly>

            <label for="especialidad">Especialidad:</label>
            <input type="text" id="especialidad" name="especialidad" value="<?php echo $especialidad; ?>" readonly>

            <h3>Horario Personalizado</h3>
            <table border="0" width="100%">
                <tr>
                    <th>Día</th>
                    <th>Horario de Mañana</th>
                    <th>Horario de Tarde</th>
                </tr>

                <?php
                // Generamos el formulario para los días de la semana
                $diasSemana = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"];
                foreach ($diasSemana as $dia) {
                    $dia_normalizado = normalizarNombreDia($dia); // Normalizar el nombre del día
                    echo '<tr>';
                    echo '<td><input type="checkbox" name="dias[]" value="' . $dia . '" ' . (isset($horarios_guardados[$dia_normalizado]) ? 'checked' : '') . '> ' . $dia . '</td>';
                    echo '<td>';
                    echo 'Inicio: <select name="horainicioman_' . $dia . '">';
                    echo '<option value="" disabled selected>Seleccione</option>';
                    generarOpcionesHorario('07:00', '12:00', $horarios_guardados[$dia_normalizado]['inicio_manana'] ?? '');
                    echo '</select>';
                    echo ' Fin: <select name="horafinman_' . $dia . '">';
                    echo '<option value="" disabled selected>Seleccione</option>';
                    generarOpcionesHorario('07:30', '12:30', $horarios_guardados[$dia_normalizado]['fin_manana'] ?? '');
                    echo '</select>';
                    echo '</td>';

                    echo '<td>';
                    echo 'Inicio: <select name="horainiciotar_' . $dia . '">';
                    echo '<option value="" disabled selected>Seleccione</option>';
                    generarOpcionesHorario('13:00', '18:00', $horarios_guardados[$dia_normalizado]['inicio_tarde'] ?? '');
                    echo '</select>';
                    echo ' Fin: <select name="horafintar_' . $dia . '">';
                    echo '<option value="" disabled selected>Seleccione</option>';
                    generarOpcionesHorario('13:30', '18:30', $horarios_guardados[$dia_normalizado]['fin_tarde'] ?? '');
                    echo '</select>';
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </table>
            <input type="submit" value="Guardar cambios" name="submitPersonalizado">
        </form>
    </div>

    <script>
        // Validación para asegurarse de que al menos un día esté seleccionado
        document.querySelector('form').addEventListener('submit', function (e) {
            const checkboxes = document.querySelectorAll('input[name="dias[]"]:checked');
            if (checkboxes.length === 0) {
                e.preventDefault();
                alert("Por favor, seleccione al menos un día.");
            }
        });
    </script>

</body>
</html>

<?php
// Procesar la actualización de los horarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitPersonalizado'])) {
    $docid = $_POST['docid'];
    $dias = $_POST['dias'] ?? [];

    // Borrar los horarios anteriores del doctor
    $sql_delete = "DELETE FROM disponibilidad_doctor WHERE docid = '$docid'";
    $database->query($sql_delete);

    // Insertar los nuevos horarios
    foreach ($dias as $dia) {
        $dia_normalizado = normalizarNombreDia($dia); // Normalizar el nombre del día
        $horainicioman = $_POST['horainicioman_' . $dia] ?? null;
        $horafinman = $_POST['horafinman_' . $dia] ?? null;
        $horainiciotar = $_POST['horainiciotar_' . $dia] ?? null;
        $horafintar = $_POST['horafintar_' . $dia] ?? null;

        $sql_insert = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar) 
                       VALUES ('$docid', '$dia', '$horainicioman', '$horafinman', '$horainiciotar', '$horafintar')";
        $database->query($sql_insert);
    }

    header("Location: horarios2.php"); // Redirigir después de guardar
}
?>
