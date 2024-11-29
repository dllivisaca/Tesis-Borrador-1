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

    // Función para comparar los horarios guardados con los nuevos
    function compararHorarios($horarios_guardados, $nuevos_horarios) {
        foreach ($nuevos_horarios as $dia => $nuevo_horario) {
            if (!isset($horarios_guardados[$dia])) {
                return false; // El día no está en los horarios guardados, hay un cambio
            }

            $horario_guardado = $horarios_guardados[$dia];
            foreach ($nuevo_horario as $clave => $valor) {
                if ($valor !== $horario_guardado[$clave]) {
                    return false; // Hay un cambio en algún valor de horario
                }
            }
        }

        // Comprobar si hay días en los horarios guardados que no están en los nuevos horarios
        foreach ($horarios_guardados as $dia => $horario_guardado) {
            if (!isset($nuevos_horarios[$dia])) {
                return false; // Hay un día que estaba antes pero ya no está, hay un cambio
            }
        }

        return true; // No hay cambios
    }

    // Procesar la actualización de los horarios fijos
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitFijo'])) {
        $docid = $_POST['docid'];
        $dias = $_POST['dias'] ?? [];

        $horainicioman = $_POST['horainicioman'] ?? null;
        $horafinman = $_POST['horafinman'] ?? null;
        $horainiciotar = $_POST['horainiciotar'] ?? null;
        $horafintar = $_POST['horafintar'] ?? null;

        // Validar que al menos un día fue seleccionado
        if (empty($dias)) {
            echo "<script>
                    alert('Por favor, selecciona al menos un día.');
                    window.history.back();
                </script>";
            exit();
        }

        // Validar que si se selecciona una hora de inicio también se haya seleccionado una hora de fin (y viceversa)
        if (($horainicioman && !$horafinman) || (!$horainicioman && $horafinman)) {
            echo "<script>
                    alert('Por favor, selecciona tanto la hora de inicio como la hora de fin para la mañana.');
                    window.location.href = 'horarios.php';
                </script>";
            exit();
        }
        if (($horainiciotar && !$horafintar) || (!$horainiciotar && $horafintar)) {
            echo "<script>
                    alert('Por favor, selecciona tanto la hora de inicio como la hora de fin para la tarde.');
                    window.location.href = 'horarios.php';
                </script>";
            exit();
        }

        // Normalizar los nuevos horarios (tratar NULL y vacío como cadenas vacías)
        $nuevo_inicio_manana = !empty($horainicioman) ? trim($horainicioman) : '';
        $nuevo_fin_manana = !empty($horafinman) ? trim($horafinman) : '';
        $nuevo_inicio_tarde = !empty($horainiciotar) ? trim($horainiciotar) : '';
        $nuevo_fin_tarde = !empty($horafintar) ? trim($horafintar) : '';

        // Cargar los horarios guardados antes de la comparación
        $sql_horarios_actuales = "SELECT * FROM disponibilidad_doctor WHERE docid = '$docid' AND tipo_horario = 'fijo'";
        $result_horarios_actuales = $database->query($sql_horarios_actuales);

        $horarios_guardados = [];
        while ($horario = $result_horarios_actuales->fetch_assoc()) {
            $dia_normalizado = normalizarNombreDia($horario['dia_semana']);
            $horarios_guardados[$dia_normalizado] = [
                'horainicioman' => isset($horario['horainicioman']) ? substr(trim($horario['horainicioman']), 0, 5) : '',
                'horafinman' => isset($horario['horafinman']) ? substr(trim($horario['horafinman']), 0, 5) : '',
                'horainiciotar' => isset($horario['horainiciotar']) ? substr(trim($horario['horainiciotar']), 0, 5) : '',
                'horafintar' => isset($horario['horafintar']) ? substr(trim($horario['horafintar']), 0, 5) : '',
            ];
        }

        // Construir los nuevos horarios para la comparación
        $nuevos_horarios = [];
        foreach ($dias as $dia) {
            $dia_normalizado = normalizarNombreDia($dia);

            $nuevos_horarios[$dia_normalizado] = [
                'horainicioman' => $nuevo_inicio_manana,
                'horafinman' => $nuevo_fin_manana,
                'horainiciotar' => $nuevo_inicio_tarde,
                'horafintar' => $nuevo_fin_tarde,
            ];
        }

        // Comparar horarios para detectar cambios
        if (compararHorarios($horarios_guardados, $nuevos_horarios)) {
            // Si no hubo cambios, simplemente redirigir sin mostrar el mensaje de cambios guardados
            echo "<script>
                    alert('No hubo cambios en los horarios.');
                    window.location.href = 'horarios.php';
                </script>";
            exit();
        }

        // Inicializar la variable para marcar si hubo cambios
        $cambios_realizados = false;

        // Iterar sobre cada día seleccionado para actualizar o insertar
        foreach ($dias as $dia) {
            $dia_normalizado = normalizarNombreDia($dia);

            // Verificar si ya existe un horario para el día
            if (isset($horarios_guardados[$dia_normalizado])) {
                $horario_existente = $horarios_guardados[$dia_normalizado];

                // Comparar los horarios existentes con los nuevos horarios
                $existe_cambio = (
                    $horario_existente['horainicioman'] !== $nuevo_inicio_manana ||
                    $horario_existente['horafinman'] !== $nuevo_fin_manana ||
                    $horario_existente['horainiciotar'] !== $nuevo_inicio_tarde ||
                    $horario_existente['horafintar'] !== $nuevo_fin_tarde
                );

                if ($existe_cambio) {
                    // Validar que la hora de fin sea mayor que la de inicio para mañana y tarde
                    if ($nuevo_inicio_manana >= $nuevo_fin_manana && $nuevo_inicio_manana !== '' && $nuevo_fin_manana !== '') {
                        echo "<script>
                                alert('La hora de inicio de la mañana debe ser anterior a la hora de fin');
                                window.location.href = 'horarios.php';
                            </script>";
                        exit();
                    }
                    if ($nuevo_inicio_tarde >= $nuevo_fin_tarde && $nuevo_inicio_tarde !== '' && $nuevo_fin_tarde !== '') {
                        echo "<script>
                                alert('La hora de inicio de la tarde debe ser anterior a la hora de fin');
                                window.location.href = 'horarios.php';
                            </script>";
                        exit();
                    }

                    // Realizar el UPDATE
                    $sql_update = "UPDATE disponibilidad_doctor 
                                SET horainicioman = " . ($nuevo_inicio_manana !== '' ? "'" . $database->real_escape_string($nuevo_inicio_manana) . "'" : "NULL") . ",
                                    horafinman = " . ($nuevo_fin_manana !== '' ? "'" . $database->real_escape_string($nuevo_fin_manana) . "'" : "NULL") . ",
                                    horainiciotar = " . ($nuevo_inicio_tarde !== '' ? "'" . $database->real_escape_string($nuevo_inicio_tarde) . "'" : "NULL") . ",
                                    horafintar = " . ($nuevo_fin_tarde !== '' ? "'" . $database->real_escape_string($nuevo_fin_tarde) . "'" : "NULL") . "
                                WHERE docid = '$docid' AND dia_semana = '$dia_normalizado' AND tipo_horario = 'fijo'";
                    if (!$database->query($sql_update)) {
                        echo "<script>
                                alert('Error al actualizar los horarios.');
                                window.location.href = 'horarios.php';
                            </script>";
                        exit();
                    }

                    $cambios_realizados = true;
                }
            } else {
                // Si no existe, realizar el INSERT
                // Validar que la hora de fin sea mayor que la de inicio para mañana y tarde
                if ($nuevo_inicio_manana >= $nuevo_fin_manana && $nuevo_inicio_manana !== '' && $nuevo_fin_manana !== '') {
                    echo "<script>
                            alert('La hora de inicio de la mañana debe ser anterior a la hora de fin');
                            window.location.href = 'horarios.php';
                        </script>";
                    exit();
                }
                if ($nuevo_inicio_tarde >= $nuevo_fin_tarde && $nuevo_inicio_tarde !== '' && $nuevo_fin_tarde !== '') {
                    echo "<script>
                            alert('La hora de inicio de la tarde debe ser anterior a la hora de fin');
                            window.location.href = 'horarios.php';
                        </script>";
                    exit();
                }

                $sql_insert = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar, tipo_horario) 
                            VALUES ('$docid', '$dia_normalizado', 
                                    " . ($nuevo_inicio_manana !== '' ? "'" . $database->real_escape_string($nuevo_inicio_manana) . "'" : "NULL") . ", 
                                    " . ($nuevo_fin_manana !== '' ? "'" . $database->real_escape_string($nuevo_fin_manana) . "'" : "NULL") . ", 
                                    " . ($nuevo_inicio_tarde !== '' ? "'" . $database->real_escape_string($nuevo_inicio_tarde) . "'" : "NULL") . ", 
                                    " . ($nuevo_fin_tarde !== '' ? "'" . $database->real_escape_string($nuevo_fin_tarde) . "'" : "NULL") . ", 
                                    'fijo')";

                if (!$database->query($sql_insert)) {
                    echo "<script>
                            alert('Error al insertar los nuevos horarios.');
                            window.location.href = 'horarios.php';
                        </script>";
                    exit();
                }

                $cambios_realizados = true;
            }
        }

        // Identificar y eliminar días que ya no están seleccionados
        $dias_para_eliminar = array_diff(array_keys($horarios_guardados), array_map('normalizarNombreDia', $dias));

        if (!empty($dias_para_eliminar)) {
            foreach ($dias_para_eliminar as $dia_eliminar) {
                $sql_delete = "DELETE FROM disponibilidad_doctor WHERE docid = '$docid' AND dia_semana = '$dia_eliminar' AND tipo_horario = 'fijo'";
                if (!$database->query($sql_delete)) {
                    echo "<script>
                            alert('Error al eliminar el horario para el día $dia_eliminar.');
                            window.location.href = 'horarios.php';
                        </script>";
                    exit();
                }
                $cambios_realizados = true;
            }
        }

        // Mostrar mensaje de confirmación y redirigir si hubo cambios
        if ($cambios_realizados) {
            echo "<script>
                    alert('Cambios guardados correctamente.');
                    window.location.href = 'horarios.php';
                </script>";
        } else {
            // Mostrar mensaje de que no hubo cambios
            echo "<script>
                    alert('No hubo cambios en los horarios.');
                    window.location.href = 'horarios.php';
                </script>";
        }
        exit();
    }


    // Procesar la actualización de los horarios personalizados **ANTES DE CUALQUIER SALIDA**
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitPersonalizado'])) {
        $docid = $_POST['docid'];
        $dias = $_POST['dias'] ?? [];

        // **Nuevo Código: Cargar los horarios guardados antes de la comparación**
        $sql_horarios_actuales = "SELECT * FROM disponibilidad_doctor WHERE docid = '$docid'";
        $result_horarios_actuales = $database->query($sql_horarios_actuales);

        $horarios_guardados = [];
        while ($horario = $result_horarios_actuales->fetch_assoc()) {
            $dia_normalizado = normalizarNombreDia($horario['dia_semana']);
            $horarios_guardados[$dia_normalizado] = [
                'inicio_manana' => empty($horario['horainicioman']) ? null : substr(normalizeTime($horario['horainicioman']), 0, 5),
                'fin_manana' => empty($horario['horafinman']) ? null : substr(normalizeTime($horario['horafinman']), 0, 5),
                'inicio_tarde' => empty($horario['horainiciotar']) ? null : substr(normalizeTime($horario['horainiciotar']), 0, 5),
                'fin_tarde' => empty($horario['horafintar']) ? null : substr(normalizeTime($horario['horafintar']), 0, 5),
            ];
        }

        // Construir nuevos horarios desde el formulario
        $nuevos_horarios = [];
        foreach ($dias as $dia) {
            $dia_normalizado = normalizarNombreDia($dia);

            $nuevos_horarios[$dia_normalizado] = [
                'inicio_manana' => empty($_POST['horainicioman_' . $dia]) ? null : substr(normalizeTime($_POST['horainicioman_' . $dia]), 0, 5),
                'fin_manana' => empty($_POST['horafinman_' . $dia]) ? null : substr(normalizeTime($_POST['horafinman_' . $dia]), 0, 5),
                'inicio_tarde' => empty($_POST['horainiciotar_' . $dia]) ? null : substr(normalizeTime($_POST['horainiciotar_' . $dia]), 0, 5),
                'fin_tarde' => empty($_POST['horafintar_' . $dia]) ? null : substr(normalizeTime($_POST['horafintar_' . $dia]), 0, 5),
            ];
        }

        // **Nueva Validación: Compleción de Horarios**
        foreach ($nuevos_horarios as $dia_normalizado => $horario) {
            // Validación de la mañana
            if (
                (!empty($horario['inicio_manana']) && empty($horario['fin_manana'])) ||
                (empty($horario['inicio_manana']) && !empty($horario['fin_manana']))
            ) {
                echo "<script>
                        alert('Para el día $dia_normalizado, si ingresas una hora de inicio de la mañana, debes ingresar también la hora de fin, y viceversa.');
                        window.location.href = 'horarios.php';
                    </script>";
                exit();
            }

            // Validación de la tarde
            if (
                (!empty($horario['inicio_tarde']) && empty($horario['fin_tarde'])) ||
                (empty($horario['inicio_tarde']) && !empty($horario['fin_tarde']))
            ) {
                echo "<script>
                        alert('Para el día $dia_normalizado, si ingresas una hora de inicio de la tarde, debes ingresar también la hora de fin, y viceversa.');
                        window.location.href = 'horarios.php';
                    </script>";
                exit();
            }
        }

        // Aquí agregar la lógica de comparación para verificar si hubo cambios
        if (compararHorarios($horarios_guardados, $nuevos_horarios)) {
            // Si no hubo cambios, simplemente redirigir sin mostrar el mensaje de cambios guardados
            echo "<script>
                    alert('No hubo cambios en los horarios.');
                    window.location.href = 'horarios.php';
                </script>";
            exit();
        }

        // Validar los horarios antes de proceder con la eliminación de los datos previos
        foreach ($nuevos_horarios as $dia_normalizado => $horario) {
            // Validar que la hora de fin de la mañana sea mayor que la de inicio
            if (!empty($horario['inicio_manana']) && !empty($horario['fin_manana']) && $horario['inicio_manana'] >= $horario['fin_manana']) {
                echo "<script>
                        alert('La hora de inicio de la mañana debe ser anterior a la hora de fin para el día $dia_normalizado.');
                        window.location.href = 'horarios.php';
                    </script>";
                exit();
            }        

            // Validar que la hora de fin de la tarde sea mayor que la de inicio
            if (!empty($horario['inicio_tarde']) && !empty($horario['fin_tarde']) && $horario['inicio_tarde'] >= $horario['fin_tarde']) {
                echo "<script>
                        alert('La hora de inicio de la tarde debe ser anterior a la hora de fin para el día $dia_normalizado.');
                        window.location.href = 'horarios.php';
                    </script>";
                exit();
            }
        }

        // Si la validación es exitosa, proceder a eliminar los horarios anteriores
        $sql_delete = "DELETE FROM disponibilidad_doctor WHERE docid = '$docid'";
        if (!$database->query($sql_delete)) {
            echo "<script>
                    alert('Error al eliminar los horarios existentes.');
                    window.location.href = 'horarios.php';
                </script>";
            exit();
        }

        // Insertar los nuevos horarios
        foreach ($nuevos_horarios as $dia_normalizado => $horario) {
            // Verificar si el día ya tiene un horario y actualizar en lugar de eliminar e insertar
            $horainicioman = $horario['inicio_manana'] ? "'" . $database->real_escape_string($horario['inicio_manana']) . "'" : "NULL";
            $horafinman = $horario['fin_manana'] ? "'" . $database->real_escape_string($horario['fin_manana']) . "'" : "NULL";
            $horainiciotar = $horario['inicio_tarde'] ? "'" . $database->real_escape_string($horario['inicio_tarde']) . "'" : "NULL";
            $horafintar = $horario['fin_tarde'] ? "'" . $database->real_escape_string($horario['fin_tarde']) . "'" : "NULL";
        
            $sql_upsert = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar, tipo_horario) 
                        VALUES ('$docid', '$dia_normalizado', 
                                $horainicioman, 
                                $horafinman, 
                                $horainiciotar, 
                                $horafintar, 
                                'personalizado')
                        ON DUPLICATE KEY UPDATE
                        horainicioman = $horainicioman, 
                        horafinman = $horafinman, 
                        horainiciotar = $horainiciotar, 
                        horafintar = $horafintar";
        
            if (!$database->query($sql_upsert)) {
                echo "<script>
                        alert('Error al insertar los nuevos horarios.');
                        window.location.href = 'horarios.php';
                    </script>";
                exit();
            }
        }

        // Mostrar mensaje de confirmación y redirigir
        echo "<script>
                alert('Cambios guardados correctamente.');
                window.location.href = 'horarios.php';
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
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../css/base.css">
        <link rel="stylesheet" href="../css/editar_horario.css">
        <!-- <style>
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
        </style> -->
    </head>
    <body>
    <div class="container">
            <div class="menu">
                <div class="profile-container">
                    <img src="../img/logo.png" alt="Logo" class="menu-logo">
                    <p class="profile-title">Administrador</p>
                </div>
                <a href="../logout.php"><button class="btn-logout">Cerrar sesión</button></a>
                <div class="linea-separadora"></div>
                <div class="menu-links">
                    <a href="dashboard.php" class="menu-link">Dashboard</a>
                    <a href="doctores.php" class="menu-link">Doctores</a>
                    <a href="pacientes.php" class="menu-link">Pacientes</a>
                    <a href="horarios.php" class="menu-link menu-link-active">Horarios disponibles</a>
                    <a href="citas.php" class="menu-link">Citas agendadas</a>
                    <a href="opiniones_recibidas.php" class="menu-link">Opiniones recibidas</a>
                </div>
            </div>

            <div class="dash-body">
                <div class="header-actions">
                <!-- Sección izquierda: Botón Atrás y barra de búsqueda -->
                <div class="header-inline">
                    <a href="horarios.php">
                        <button class="btn-action">← Atrás</button>
                    </a>
                    <p class="heading-main12" style="margin: 0; font-size: 17px; color: rgb(49, 49, 49); align-self: left;">
                    Editar horario
                    </p>
                </div>
            </div>
        
            
                

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
                            'inicio_manana' => empty($horario['horainicioman']) ? null : substr(normalizeTime($horario['horainicioman']), 0, 5),
                            'fin_manana' => empty($horario['horafinman']) ? null : substr(normalizeTime($horario['horafinman']), 0, 5),
                            'inicio_tarde' => empty($horario['horainiciotar']) ? null : substr(normalizeTime($horario['horainiciotar']), 0, 5),
                            'fin_tarde' => empty($horario['horafintar']) ? null : substr(normalizeTime($horario['horafintar']), 0, 5),
                        ];
                    }
                }
                ?>
                <div class="header-container">
                    <div class="info-section">
                        <div class="info-label">Nombre del Doctor:</div>
                        <div class="info-text"><?php echo $doctor['docnombre']; ?></div>
                    </div>

                    <div class="info-section">
                        <div class="info-label">Especialidad:</div>
                        <div class="info-text"><?php echo $especialidad; ?></div>
                    </div>     
                </div>     

                    <!-- Pestañas -->
                    <div style="text-align: center; width: 100%;">
                        <div class="buttons-wrapper">
                            <div class="buttons-container">
                                <?php if ($tipo_horario === 'fijo') : ?>
                                    <button type="button" class="tab_btn active disabled" disabled>Horario Fijo</button>
                                    <button type="button" class="tab_btn disabled" disabled>Horario Personalizado</button>
                                <?php elseif ($tipo_horario === 'personalizado') : ?>
                                    <button type="button" class="tab_btn disabled" disabled>Horario Fijo</button>
                                    <button type="button" class="tab_btn active disabled" disabled>Horario Personalizado</button>
                                <?php else : ?>
                                    <button type="button" class="tab_btn">Horario Fijo</button>
                                    <button type="button" class="tab_btn">Horario Personalizado</button>
                                <?php endif; ?>
                                <div class="line"></div>
                            </div>
                        </div>
                        
                    </div>
                    
                
                <div class="content_wrapper">
                     <!-- Contenido de Horario Fijo -->
                     <?php if ($tipo_horario === 'fijo') : ?>
                        <div class="content_box">
                        <div class="content active">
                                <h3>Horario Fijo</h3>
                                <form id="horarioFijoForm" action="" method="POST"onsubmit="return validarHorarios(this);">
                                    <input type="hidden" name="docid" value="<?php echo $docid; ?>">
                                    <table class="horario-table" border="0">
                                        <tr>
                                            <th>Día</th>
                                        </tr>
                                        <tr>
                                            <?php
                                            // Días de la semana
                                            $diasSemana = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"];
                                            
                                            foreach ($diasSemana as $dia) {
                                                $dia_normalizado = normalizarNombreDia($dia);
                                                $horario_guardado = isset($horarios_guardados[$dia_normalizado]);
                                                echo '<td><input type="checkbox" name="dias[]" value="' . $dia . '" ' . ($horario_guardado ? 'checked' : '') . '> ' . $dia . '</td>';
                                            }
                                            ?>
                                        </tr>
                                        <tr>
                                            <td colspan="7">
                                                <h4>Horario de Mañana</h4>
                                                Inicio: <select name="horainicioman">
                                                    <option value="">Seleccione</option>
                                                    <?php generarOpcionesHorario('07:00', '12:00', $horarios_guardados[array_key_first($horarios_guardados)]['inicio_manana'] ?? ''); ?>
                                                </select>
                                                Fin: <select name="horafinman">
                                                    <option value="">Seleccione</option>
                                                    <?php generarOpcionesHorario('07:30', '12:30', $horarios_guardados[array_key_first($horarios_guardados)]['fin_manana'] ?? ''); ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="7">
                                                <h4>Horario de Tarde</h4>
                                                Inicio: <select name="horainiciotar">
                                                    <option value="">Seleccione</option>
                                                    <?php generarOpcionesHorario('13:00', '18:00', $horarios_guardados[array_key_first($horarios_guardados)]['inicio_tarde'] ?? ''); ?>
                                                </select>
                                                Fin: <select name="horafintar">
                                                    <option value="">Seleccione</option>
                                                    <?php generarOpcionesHorario('13:30', '18:30', $horarios_guardados[array_key_first($horarios_guardados)]['fin_tarde'] ?? ''); ?>
                                                </select>
                                            </td>
                                        </tr>
                                        
                                    </table>
                                    <input type="submit" value="Guardar cambios" name="submitFijo">
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Contenido de Horario Personalizado -->
                        <?php if ($tipo_horario === 'personalizado') : ?>
                        <div class="content_box">
                            
                                <div class="content active">
                                    
                                    <!-- <form id="horarioPersonalizadoForm" action="" method="POST"> -->
                                    <form id="horarioPersonalizadoForm" action="" method="POST" onsubmit="return validarHorariosPersonalizados(this);">
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
                                                echo '<select name="horainicioman_' . $dia . '">';
                                                echo '<option value="" ' . (empty($horario_guardado['inicio_manana']) ? 'selected' : '') . '>--Elija--</option>';
                                                generarOpcionesHorario('07:00', '12:00', $horario_guardado['inicio_manana'] ?? '');
                                                echo '</select>';
                                                echo ' <select name="horafinman_' . $dia . '">';
                                                echo '<option value="" ' . (empty($horario_guardado['fin_manana']) ? 'selected' : '') . '>--Elija--</option>';
                                                generarOpcionesHorario('07:30', '12:30', $horario_guardado['fin_manana'] ?? '');
                                                echo '</select>';
                                                echo '</td>';

                                                // Horario de tarde
                                                echo '<td>';
                                                echo '<select name="horainiciotar_' . $dia . '">';
                                                echo '<option value="" ' . (empty($horario_guardado['inicio_tarde']) ? 'selected' : '') . '>--Elija--</option>';
                                                generarOpcionesHorario('13:00', '18:00', $horario_guardado['inicio_tarde'] ?? '');
                                                echo '</select>';
                                                echo '<select name="horafintar_' . $dia . '">';
                                                echo '<option value="" ' . (empty($horario_guardado['fin_tarde']) ? 'selected' : '') . '>--Elija--</option>';
                                                generarOpcionesHorario('13:30', '18:30', $horario_guardado['fin_tarde'] ?? '');
                                                echo '</select>';
                                                echo '</td>';

                                                echo '</tr>';
                                            }
                                            ?>
                                            <!-- <tr>
                                                <td colspan="3" style="text-align: center;">
                                                    <input type="submit" value="Guardar cambios" name="submitPersonalizado">
                                                </td>
                                                
                                            </tr> -->
                                            
                                        </table>
                                        <div class="boton-centrado">
                                            <input type="submit" value="Guardar cambios" name="submitPersonalizado">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                        
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

            // Función para validar horarios fijos
            function validarHorarios(formulario) {
                // Verificar si al menos un día está seleccionado
                const diasSeleccionados = formulario.querySelectorAll('input[name="dias[]"]:checked');
                if (diasSeleccionados.length === 0) {
                    alert("Por favor, selecciona al menos un día.");
                    return false;
                }
                // Obtener los valores de los inputs
                const horaInicioManana = formulario.querySelector('select[name="horainicioman"]').value;
                    const horaFinManana = formulario.querySelector('select[name="horafinman"]').value;
                    const horaInicioTarde = formulario.querySelector('select[name="horainiciotar"]').value;
                    const horaFinTarde = formulario.querySelector('select[name="horafintar"]').value;

                    // Validación para la mañana: si se selecciona una hora de inicio, también debe haber una hora de fin, y viceversa.
                    if ((horaInicioManana && !horaFinManana) || (!horaInicioManana && horaFinManana)) {
                        alert("Por favor, selecciona tanto la hora de inicio como la hora de fin para la mañana.");
                        return false;
                    }

                    // Validación para la tarde: si se selecciona una hora de inicio, también debe haber una hora de fin, y viceversa.
                    if ((horaInicioTarde && !horaFinTarde) || (!horaInicioTarde && horaFinTarde)) {
                        alert("Por favor, selecciona tanto la hora de inicio como la hora de fin para la tarde.");
                        return false;
                    }

                    // Validación de que la hora de inicio de la mañana sea anterior a la de fin
                    if (horaInicioManana && horaFinManana && horaInicioManana >= horaFinManana) {
                        alert("La hora de inicio de la mañana debe ser anterior a la hora de fin.");
                        return false;
                    }

                    // Validación de que la hora de inicio de la tarde sea anterior a la de fin
                    if (horaInicioTarde && horaFinTarde && horaInicioTarde >= horaFinTarde) {
                        alert("La hora de inicio de la tarde debe ser anterior a la hora de fin.");
                        return false;
                    }

                    // Validar que al menos un horario esté ingresado (mañana o tarde)
                    if ((!horaInicioManana && !horaFinManana) && (!horaInicioTarde && !horaFinTarde)) {
                        alert("Por favor, ingresa al menos un horario válido para la mañana o la tarde.");
                        return false;
                    }

                    // Si todo está bien, retornamos true para permitir que el formulario se envíe
                    return true;
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

            document.addEventListener("DOMContentLoaded", function() {
                const tabs = document.querySelectorAll('.tab_btn');
                const line = document.querySelector('.line');

                function updateLinePosition(activeTab) {
                    line.style.width = `${activeTab.offsetWidth}px`;
                    line.style.left = `${activeTab.offsetLeft}px`;
                }

                tabs.forEach((tab) => {
                    tab.addEventListener('click', (e) => { 
                        tabs.forEach(tab => tab.classList.remove('active')); // Quitar clase activa
                        e.target.classList.add('active'); // Añadir clase activa al clicado
                        updateLinePosition(e.target); // Actualizar línea azul
                    });
                });

                const initialActiveTab = document.querySelector('.tab_btn.active');
                if (initialActiveTab) {
                    updateLinePosition(initialActiveTab);
                }
            });



            // Función para manejar pestañas
            /* document.addEventListener("DOMContentLoaded", function() {
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
                }); */

                // Mostrar pestaña por defecto si es necesario
                // tabs[0].click(); 
            ;
        </script>
    </body>
    </html>