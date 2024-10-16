<?php
session_start();

if(isset($_SESSION["usuario"])){
    if(($_SESSION["usuario"])=="" or $_SESSION['usuario_rol']!='adm'){
        header("location: ../login.php");
        exit();
    }
}else{
    header("location: ../login.php");
    exit();
}

// Importar la base de datos
include("../conexion_db.php");

// Procesar la actualización de los horarios **ANTES DE CUALQUIER SALIDA**
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitPersonalizado'])) {
    $docid = $_POST['docid'];
    $dias = $_POST['dias'] ?? [];

    // Borrar los horarios anteriores del doctor
    $sql_delete = "DELETE FROM disponibilidad_doctor WHERE docid = '$docid'";
    $database->query($sql_delete);

    // Insertar los nuevos horarios
    foreach ($dias as $dia) {
        $horainicioman = $_POST['horainicioman_' . $dia] ?? null;
        $horafinman = $_POST['horafinman_' . $dia] ?? null;
        $horainiciotar = $_POST['horainiciotar_' . $dia] ?? null;
        $horafintar = $_POST['horafintar_' . $dia] ?? null;

        // Convertir valores vacíos a NULL
        $horainicioman = !empty($horainicioman) ? $horainicioman : null;
        $horafinman = !empty($horafinman) ? $horafinman : null;
        $horainiciotar = !empty($horainiciotar) ? $horainiciotar : null;
        $horafintar = !empty($horafintar) ? $horafintar : null;

        $sql_insert = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar) 
                       VALUES ('$docid', '$dia', 
                               " . ($horainicioman ? "'$horainicioman'" : "NULL") . ", 
                               " . ($horafinman ? "'$horafinman'" : "NULL") . ", 
                               " . ($horainiciotar ? "'$horainiciotar'" : "NULL") . ", 
                               " . ($horafintar ? "'$horafintar'" : "NULL") . ")";
        $database->query($sql_insert);
    }

    header("Location: horarios2.php"); // Redirigir después de guardar
    exit();
}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Horarios</title>
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .tab_box {
            display: flex;
            position: relative;
            margin: 20px 0;
        }
        .tab_btn {
            padding: 10px 20px;
            cursor: default; /* Cambiar cursor a default */
            background: none;
            border: none;
            outline: none;
            font-size: 16px;
            transition: 0.3s;
            color: gray; /* Mostrar en gris para indicar que está deshabilitada */
        }
        .tab_btn.active {
            color: #007BFF;
        }
        /* Estilo para deshabilitar las pestañas */
        .tab_btn.disabled {
            color: gray;
            cursor: not-allowed;
        }
        .line {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 0;
            background: #007BFF;
            transition: 0.3s;
        }
        .content {
            display: none;
        }
        .content.active {
            display: block;
        }
        .info-label {
            font-weight: bold;
            margin-top: 10px;
        }
        .info-text {
            margin-bottom: 15px;
        }
        /* Estilos para ajustar el ancho de las columnas */
        .horario-table {
            width: 100%;
            border-collapse: collapse;
        }
        .horario-table th, .horario-table td {
            padding: 10px;
            text-align: left;
        }
        .horario-table th:nth-child(1), .horario-table td:nth-child(1) {
            width: 25%;
        }
        .horario-table th:nth-child(2), .horario-table td:nth-child(2),
        .horario-table th:nth-child(3), .horario-table td:nth-child(3) {
            width: 37.5%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="menu">
            <!-- Menú lateral -->
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px" >
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title">Administrador</p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <a href="../logout.php" ><input type="button" value="Cerrar sesión" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor">
                        <a href="doctores.php" class="non-style-link-menu"><div><p class="menu-text">Doctores</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-patient">
                        <a href="pacientes.php" class="non-style-link-menu"><div><p class="menu-text">Pacientes</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="citas.php" class="non-style-link-menu"><div><p class="menu-text">Citas agendadas</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-schedule menu-active menu-icon-schedule-active">
                        <a href="horarios2.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Horarios disponibles</p></div></a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style="margin-top:25px;">
                <tr>
                    <td width="13%">
                        <a href="horarios2.php" ><button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Editar Horario</p>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);text-align: right;">
                            Fecha de hoy
                        </p>
                        <p class="heading-sub12" style="margin: 0;">
                            <?php 
                            date_default_timezone_set('America/Mexico_City');
                            $today = date('Y-m-d');
                            echo $today;
                            ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
            </table>

            <?php
            if ($_GET) {
                $docid = $_GET['id'];

                // Consultar los datos del doctor y su disponibilidad actual
                $sql = "SELECT doctor.docnombre, doctor.especialidades 
                        FROM doctor 
                        WHERE doctor.docid = '$docid'";
                $result = $database->query($sql);
                $doctor = $result->fetch_assoc();

                // Obtener especialidad del doctor
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
                <div class="info-label">Nombre del Doctor:</div>
                <div class="info-text"><?php echo $doctor['docnombre']; ?></div>

                <div class="info-label">Especialidad:</div>
                <div class="info-text"><?php echo $especialidad; ?></div>

                <!-- Pestañas -->
                <div class="tab_box">
                    <button type="button" class="tab_btn disabled" disabled>Horario Fijo</button>
                    <button type="button" class="tab_btn disabled active" disabled>Horario Personalizado</button>
                    <div class="line"></div>
                </div>

                <!-- Contenido de Horario Personalizado -->
                <div class="content_box">
                    <div class="content active">
                        <h3>Horario Personalizado</h3>
                        <!-- Formulario único -->
                        <form id="horarioPersonalizadoForm" action="" method="POST">
                            <!-- Aseguramos que el campo docid esté dentro del formulario -->
                            <input type="hidden" name="docid" value="<?php echo $docid; ?>">

                            <table class="horario-table" border="0">
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
                                <tr>
                                    <td colspan="3" style="text-align: center;">
                                        <input type="submit" value="Guardar cambios" name="submitPersonalizado">
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
            </div>

            
        </div>
    </div>
</body>
</html>