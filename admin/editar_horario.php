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

// Función para normalizar tiempos, tratando NULL y vacío como cadena vacía
function normalizeTime($time) {
    return isset($time) ? trim($time) : '';
}

// Procesar la actualización de los horarios **ANTES DE CUALQUIER SALIDA**
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitPersonalizado'])) {
    $docid = $_POST['docid'];
    $dias = $_POST['dias'] ?? [];

    // Obtener horarios actuales del doctor
    $sql_horarios = "SELECT * FROM disponibilidad_doctor WHERE docid = '$docid'";
    $result_horarios = $database->query($sql_horarios);
    $horarios_guardados = [];

    while ($horario = $result_horarios->fetch_assoc()) {
        $dia_normalizado = normalizarNombreDia($horario['dia_semana']);
        $horarios_guardados[$dia_normalizado] = [
            'inicio_manana' => substr(normalizeTime($horario['horainicioman']), 0, 5),  // Aplicar normalizeTime
            'fin_manana' => substr(normalizeTime($horario['horafinman']), 0, 5),
            'inicio_tarde' => substr(normalizeTime($horario['horainiciotar']), 0, 5),
            'fin_tarde' => substr(normalizeTime($horario['horafintar']), 0, 5)
        ];
    }

    // Construir nuevos horarios desde el formulario
    $nuevos_horarios = [];
    foreach ($dias as $dia) {
        $dia_normalizado = normalizarNombreDia($dia);

        $nuevos_horarios[$dia_normalizado] = [
            'inicio_manana' => substr(normalizeTime($_POST['horainicioman_' . $dia] ?? ''), 0, 5),
            'fin_manana' => substr(normalizeTime($_POST['horafinman_' . $dia] ?? ''), 0, 5),
            'inicio_tarde' => substr(normalizeTime($_POST['horainiciotar_' . $dia] ?? ''), 0, 5),
            'fin_tarde' => substr(normalizeTime($_POST['horafintar_' . $dia] ?? ''), 0, 5),
        ];
    }

    // Ordenar los arrays por clave para asegurar consistencia en el orden
    ksort($horarios_guardados);
    ksort($nuevos_horarios);

    // Serializar y comparar hashes
    $hash_guardados = md5(serialize($horarios_guardados));
    $hash_nuevos = md5(serialize($nuevos_horarios));

    // **Opcional: Depuración**
    /*
    error_log("Horarios Guardados: " . print_r($horarios_guardados, true));
    error_log("Nuevos Horarios: " . print_r($nuevos_horarios, true));
    error_log("Hash Guardados: " . $hash_guardados);
    error_log("Hash Nuevos: " . $hash_nuevos);
    */

    // Comparar los hashes para determinar si hubo cambios
    if ($hash_guardados !== $hash_nuevos) {
        $cambios_realizados = true;
    } else {
        $cambios_realizados = false;
    }

    // Si no hubo cambios, mostrar un mensaje y redirigir
    if (!$cambios_realizados) {
        echo "<script>
                alert('No se realizaron cambios.');
                window.location.href = 'horarios2.php';
              </script>";
        exit();
    }

    // Borrar los horarios anteriores del doctor
    $sql_delete = "DELETE FROM disponibilidad_doctor WHERE docid = '$docid'";
    if (!$database->query($sql_delete)) {
        echo "<script>
                alert('Error al eliminar los horarios existentes.');
                window.location.href = 'horarios2.php';
              </script>";
        exit();
    }

    // Insertar los nuevos horarios
    foreach ($nuevos_horarios as $dia_normalizado => $horario) {
        // Convertir valores vacíos a NULL
        $horainicioman = !empty($horario['inicio_manana']) ? "'" . $database->real_escape_string($horario['inicio_manana']) . "'" : "NULL";
        $horafinman = !empty($horario['fin_manana']) ? "'" . $database->real_escape_string($horario['fin_manana']) . "'" : "NULL";
        $horainiciotar = !empty($horario['inicio_tarde']) ? "'" . $database->real_escape_string($horario['inicio_tarde']) . "'" : "NULL";
        $horafintar = !empty($horario['fin_tarde']) ? "'" . $database->real_escape_string($horario['fin_tarde']) . "'" : "NULL";

        // Agregar tipo_horario como 'personalizado'
        $dia_original = htmlspecialchars($dia_normalizado); // Asegurar que no haya inyección
        $sql_insert = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar, tipo_horario) 
                       VALUES ('$docid', '$dia_original', 
                               $horainicioman, 
                               $horafinman, 
                               $horainiciotar, 
                               $horafintar, 
                               'personalizado')";

        if (!$database->query($sql_insert)) {
            echo "<script>
                    alert('Error al insertar los nuevos horarios.');
                    window.location.href = 'horarios2.php';
                  </script>";
            exit();
        }
    }

    // Mostrar mensaje de confirmación y redirigir
    echo "<script>
            alert('Cambios guardados correctamente.');
            window.location.href = 'horarios2.php';
          </script>";
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

                // Consulta para obtener el tipo de horario guardado del doctor
                $sql_tipo_horario = "SELECT tipo_horario FROM disponibilidad_doctor WHERE docid = '$docid' LIMIT 1";
                $result_tipo_horario = $database->query($sql_tipo_horario);
                $tipo_horario = $result_tipo_horario->num_rows > 0 ? $result_tipo_horario->fetch_assoc()['tipo_horario'] : null;

                // Consulta para obtener los horarios guardados del doctor
                $sql_horarios = "SELECT * FROM disponibilidad_doctor WHERE docid = '$docid'";
                $result_horarios = $database->query($sql_horarios);

                $horarios_guardados = [];
                while ($horario = $result_horarios->fetch_assoc()) {
                    $dia_normalizado = normalizarNombreDia($horario['dia_semana']);
                    $horarios_guardados[$dia_normalizado] = [
                        'inicio_manana' => substr(normalizeTime($horario['horainicioman']), 0, 5),
                        'fin_manana' => substr(normalizeTime($horario['horafinman']), 0, 5),
                        'inicio_tarde' => substr(normalizeTime($horario['horainiciotar']), 0, 5),
                        'fin_tarde' => substr(normalizeTime($horario['horafintar']), 0, 5),
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
                    <?php if ($tipo_horario === 'fijo') : ?>
                        <button type="button" class="tab_btn active">Horario Fijo</button>
                        <button type="button" class="tab_btn disabled" disabled>Horario Personalizado</button>
                    <?php elseif ($tipo_horario === 'personalizado') : ?>
                        <button type="button" class="tab_btn disabled" disabled>Horario Fijo</button>
                        <button type="button" class="tab_btn active">Horario Personalizado</button>
                    <?php else : ?>
                        <button type="button" class="tab_btn">Horario Fijo</button>
                        <button type="button" class="tab_btn">Horario Personalizado</button>
                    <?php endif; ?>
                    <div class="line"></div>
                </div>

                <!-- Contenido de Horario Personalizado -->
                <div class="content_box">
                    <?php if ($tipo_horario === 'personalizado') : ?>
                        <!-- Contenido de Horario Personalizado -->
                        <div class="content active">
                            <h3>Horario Personalizado</h3>
                            <form id="horarioPersonalizadoForm" action="" method="POST">
                                <input type="hidden" name="docid" value="<?php echo $docid; ?>">
                                <table class="horario-table" border="0">
                                    <tr>
                                        <th>Día</th>
                                        <th>Horario de Mañana</th>
                                        <th>Horario de Tarde</th>
                                    </tr>
                                    <?php
                                    // Días de la semana
                                    $diasSemana = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"];
                                    
                                    foreach ($diasSemana as $dia) {
                                        $dia_normalizado = normalizarNombreDia($dia); // Normalizar el nombre del día
                                        
                                        // Verificar si el día tiene horario guardado
                                        $horario_guardado = $horarios_guardados[$dia_normalizado] ?? null;

                                        // Mostrar la fila para cada día
                                        echo '<tr>';
                                        echo '<td><input type="checkbox" name="dias[]" value="' . $dia . '" ' . ($horario_guardado ? 'checked' : '') . '> ' . $dia . '</td>';

                                        // Horario de mañana
                                        echo '<td>';
                                        echo 'Inicio: <select name="horainicioman_' . $dia . '">';
                                        echo '<option value="" disabled ' . (!$horario_guardado ? 'selected' : '') . '>Seleccione</option>';
                                        generarOpcionesHorario('07:00', '12:00', $horario_guardado['inicio_manana'] ?? '');
                                        echo '</select>';
                                        echo ' Fin: <select name="horafinman_' . $dia . '">';
                                        echo '<option value="" disabled ' . (!$horario_guardado ? 'selected' : '') . '>Seleccione</option>';
                                        generarOpcionesHorario('07:30', '12:30', $horario_guardado['fin_manana'] ?? '');
                                        echo '</select>';
                                        echo '</td>';

                                        // Aquí es donde haces el cambio en la generación de las opciones de la tarde
                                        // Horario de tarde
                                        echo '<td>';
                                        echo 'Inicio: <select name="horainiciotar_' . $dia . '">';
                                        echo '<option value="" ' . (!$horario_guardado || empty($horario_guardado['inicio_tarde']) ? 'selected' : '') . '>Seleccione</option>';
                                        generarOpcionesHorario('13:00', '18:00', $horario_guardado['inicio_tarde'] ?? '');
                                        echo '</select>';
                                        echo ' Fin: <select name="horafintar_' . $dia . '">';
                                        echo '<option value="" ' . (!$horario_guardado || empty($horario_guardado['fin_tarde']) ? 'selected' : '') . '>Seleccione</option>';
                                        generarOpcionesHorario('13:30', '18:30', $horario_guardado['fin_tarde'] ?? '');
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
                    <?php endif; ?>
                </div>
        </div>

    </div>

    <!-- Scripts de Validación y Generación de Horarios -->
    <script>
        // Función para generar opciones de horarios en intervalos de 30 minutos
        function generarOpcionesHorario(selectElement, horaInicio, horaFin, valorSeleccionado = '') {
            const minutosIntervalo = 30;
            const [horaInicial, minutoInicial] = horaInicio.split(':').map(Number);
            const [horaFinal, minutoFinal] = horaFin.split(':').map(Number);

            selectElement.innerHTML = ''; // Limpiar opciones previas

            // Agregar opción por defecto vacía y deshabilitada
            const opcionDefault = new Option('Seleccione', '', true, true);
            opcionDefault.disabled = true;
            selectElement.add(opcionDefault);

            for (let hora = horaInicial; hora <= horaFinal; hora++) {
                for (let minuto = 0; minuto < 60; minuto += minutosIntervalo) {
                    if (hora === horaFinal && minuto >= minutoFinal) break;

                    const horaFormateada = `${hora.toString().padStart(2, '0')}:${minuto.toString().padStart(2, '0')}`;
                    const opcion = new Option(horaFormateada, horaFormateada, false, horaFormateada === valorSeleccionado);
                    selectElement.add(opcion);
                }
            }
        }

        // Función para validar horarios personalizados
        function validarHorariosPersonalizados(formulario) {
            const diasSeleccionados = formulario.querySelectorAll('input[name="dias[]"]:checked');
            let valid = true;

            // Asegurarse de que al menos un día esté seleccionado
            if (diasSeleccionados.length === 0) {
                alert("Por favor, selecciona al menos un día.");
                return false;
            }

            diasSeleccionados.forEach((diaCheckbox) => {
                const dia = diaCheckbox.value;
                const horaInicioManana = formulario.querySelector(`select[name="horainicioman_${dia}"]`).value;
                const horaFinManana = formulario.querySelector(`select[name="horafinman_${dia}"]`).value;
                const horaInicioTarde = formulario.querySelector(`select[name="horainiciotar_${dia}"]`).value;
                const horaFinTarde = formulario.querySelector(`select[name="horafintar_${dia}"]`).value;

                // Validación de la mañana: si se ingresa una hora de inicio, también se debe ingresar la hora de fin y viceversa
                if ((horaInicioManana && !horaFinManana) || (!horaInicioManana && horaFinManana)) {
                    alert(`Por favor, ingresa tanto la hora de inicio como la hora de fin de la mañana para el día ${dia}.`);
                    valid = false;
                }

                // Validación de la tarde: si se ingresa una hora de inicio, también se debe ingresar la hora de fin y viceversa
                if ((horaInicioTarde && !horaFinTarde) || (!horaInicioTarde && horaFinTarde)) {
                    alert(`Por favor, ingresa tanto la hora de inicio como la hora de fin de la tarde para el día ${dia}.`);
                    valid = false;
                }

                // Validación de que la hora de inicio de la mañana sea anterior a la de fin
                if (horaInicioManana && horaFinManana && horaInicioManana >= horaFinManana) {
                    alert(`En el día ${dia}, el horario de inicio de la mañana debe ser anterior al horario de fin.`);
                    valid = false;
                }

                // Validación de que la hora de inicio de la tarde sea anterior a la de fin
                if (horaInicioTarde && horaFinTarde && horaInicioTarde >= horaFinTarde) {
                    alert(`En el día ${dia}, el horario de inicio de la tarde debe ser anterior al horario de fin.`);
                    valid = false;
                }

                // Validar que al menos un horario (mañana o tarde) esté ingresado para cada día seleccionado
                if (!horaInicioManana && !horaFinManana && !horaInicioTarde && !horaFinTarde) {
                    alert(`Por favor, ingresa al menos un horario válido (mañana o tarde) para el día ${dia}.`);
                    valid = false;
                }
            });

            return valid;
        }

        // Función para manejar pestañas
        document.addEventListener("DOMContentLoaded", function() {
            const tabs = document.querySelectorAll('.tab_btn');
            const all_content = document.querySelectorAll('.content_box');

            tabs.forEach((tab, index) => {
                tab.addEventListener('click', (e) => { 
                    // Desactivar todas las pestañas
                    tabs.forEach(tab => { tab.classList.remove('active') });
                    // Activar la pestaña clicada
                    tab.classList.add('active');
                    // Mover la línea indicadora
                    const line = document.querySelector('.line');
                    line.style.width = e.target.offsetWidth + "px"; 
                    line.style.left = e.target.offsetLeft + "px"; 
                    // Ocultar todo el contenido
                    all_content.forEach(content => { content.classList.remove('active') });
                    // Mostrar el contenido correspondiente
                    all_content[index].classList.add('active');
                })
            });

            // Mostrar pestaña por defecto si es necesario
            // tabs[0].click(); 
        });
    </script>
</body>
</html>