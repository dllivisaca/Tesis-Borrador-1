<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/admin/agregar_horario.css">
        
    <title>Horarios</title>
</head>
<body>
    <?php
    error_reporting(E_ERROR | E_PARSE);

    session_start();    

    // Mostrar el pop-up si se ha agregado el horario correctamente
    if (isset($_SESSION['success_message'])) {
        // Usar json_encode para manejar correctamente las comillas y caracteres especiales
        $message = json_encode($_SESSION['success_message']);
        echo "
        <script>
            alert($message);
            window.location.href = 'horarios.php';
        </script>
        ";
        unset($_SESSION['success_message']);
    }

    // Mostrar error si algo salió mal
    if (isset($_SESSION['error_message'])) {
        echo "<p style='color: red;'>" . $_SESSION['error_message'] . "</p>";
        unset($_SESSION['error_message']);
    }

    if (isset($_SESSION["usuario"])) {
        if (($_SESSION["usuario"]) == "" || $_SESSION['usuario_rol'] != 'adm') {
            header("location: ../login.php");
            exit;
        }
    } else {
        header("location: ../login.php");
        exit;
    }

    // Importar la conexión a la base de datos
    include("../conexion_db.php");

    // Procesar los datos del formulario
            if (isset($_POST['shedulesubmit'])|| isset($_POST['submitPersonalizado'])) {
                // Recoger los datos del formulario
                $doctor_id = $_GET['id']; 

                // Lógica para el horario fijo
                if (isset($_POST['shedulesubmit'])) {
                    $days_selected = $_POST['day_schedule'] ?? [];
                    $horainicioman = $_POST['horainicioman'];
                    $horafinman = $_POST['horafinman'];
                    $horainiciotar = $_POST['horainiciotar'];
                    $horafintar = $_POST['horafintar'];

                    foreach ($days_selected as $day) {
                        // Agregamos el campo tipo_horario con valor 'fijo'
                        $sql = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar,tipo_horario)
                                VALUES ('$doctor_id', '$day', '$horainicioman', '$horafinman', '$horainiciotar', '$horafintar','fijo')";
            
                        if ($database->query($sql)) {
                            $_SESSION['success_message'] = "Horario agregado correctamente.";
                        } else {
                            $_SESSION['error_message'] = "Error al agregar el horario" . $database->error;
                        }
                    }
                }

                // Lógica para el horario personalizado
                if (isset($_POST['submitPersonalizado'])) {
                    $dias = $_POST['dias'] ?? [];

                    if (!empty($dias)) {
                        foreach ($dias as $dia) {
                            $horainicioman = $_POST['horainicioman_' . $dia] ?? null;
                            $horafinman = $_POST['horafinman_' . $dia] ?? null;
                            $horainiciotar = $_POST['horainiciotar_' . $dia] ?? null;
                            $horafintar = $_POST['horafintar_' . $dia] ?? null;

                            $sql = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar,tipo_horario)
                                    VALUES ('$doctor_id', '$dia', '$horainicioman', '$horafinman', '$horainiciotar', '$horafintar','personalizado')";

                            if ($database->query($sql)) {
                                $_SESSION['success_message'] = "Horario agregado correctamente.";
                            } else {
                                $_SESSION['error_message'] = "Error al agregar el horario" . $database->error;
                            }
                        }
                    }
                }

                 // Redirigir después del procesamiento
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $doctor_id);
                exit;
            }
            ?>
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
                    <p class="heading-main12">Agregar horario</p>
                </div>
            </div>
        
      

            <?php
            if($_GET){
                $id=$_GET["id"];
                
                    $sqlmain= "select * from doctor where docid='$id'";
                    $result= $database->query($sqlmain);
                    $row=$result->fetch_assoc();
                    $docnombre=$row["docnombre"];
                    
                    $espe=$row["especialidades"];
                    
                    $especial_res= $database->query("select espnombre from especialidades where id='$espe'");
                    $especial_array= $especial_res->fetch_assoc();
                    $especial_name=$especial_array["espnombre"];
                    
                }
            
                    ?>
            
                <div class="header-container">
                    <div class="info-section">
                        <div class="info-label">Nombre del Doctor:</div>
                        <div class="info-text"><?php echo $docnombre; ?></div>
                    </div>

                    <div class="info-section">
                        <div class="info-label">Especialidad:</div>
                        <div class="info-text"><?php echo $especial_name; ?></div>
                    </div>
                </div>

                <div class="tab-container">
                <div class="buttons-wrapper">
                    <div class="buttons-container">
                        <button type="button" class="tab_btn active">Horario Fijo</button>
                        <button type="button" class="tab_btn">Horario Personalizado</button>
                        <div class="line"></div>
                    </div>
                </div>
           
            <!-- <div class="tab_box">
                <button class="tab_btn">Horario fijo</button>
                <button class="tab_btn">Horario personalizado</button>
                <div class="line"></div>
            </div> 
           -->

           <div class="content_wrapper">
                <!-- Contenido de Horario Fijo -->
                <div class="content_box horario-fijo active" id="tab-fijo">
                    <div class="content">
                    
                    <?php
                    if ($_GET) {
                        $id = $_GET["id"];
                        
                        // Consultar al doctor usando el ID del doctor
                        $sqlmain = "SELECT * FROM doctor WHERE docid='$id'";
                        $result = $database->query($sqlmain);
                        $row = $result->fetch_assoc();
                        $docnombre = $row["docnombre"];
                        
                        $espe = $row["especialidades"];
                        $especial_res = $database->query("SELECT espnombre FROM especialidades WHERE id='$espe'");
                        $especial_array = $especial_res->fetch_assoc();
                        $especial_name = $especial_array["espnombre"];
                        
                        // Mostrar el formulario con los días y horas
                        echo '
                            <div>
                                <table>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <form id="horarioFijoForm" action="" method="POST" class="add-new-form">
                                                <table class="horario-table" border="0">
                                                    <tr>
                                                        <th>Día</th>
                                                        <th>Horario de Mañana</th>
                                                        <th>Horario de Tarde</th>
                                                    </tr>
                                                    <tr>
                                                        <td class="dias-column">
                                                            <!-- Días de la semana con checkboxes -->
                                                            <input type="checkbox" id="checkboxLunes" name="day_schedule[]" value="Lunes"> <label for="checkboxLunes">Lunes</label><br>
                                                            <input type="checkbox" id="checkboxMartes" name="day_schedule[]" value="Martes"> <label for="checkboxMartes">Martes</label><br>
                                                            <input type="checkbox" id="checkboxMiercoles" name="day_schedule[]" value="Miercoles"> <label for="checkboxMiercoles">Miércoles</label><br>
                                                            <input type="checkbox" id="checkboxJueves" name="day_schedule[]" value="Jueves"> <label for="checkboxJueves">Jueves</label><br>
                                                            <input type="checkbox" id="checkboxViernes" name="day_schedule[]" value="Viernes"> <label for="checkboxViernes">Viernes</label><br>
                                                            <input type="checkbox" id="checkboxSabado" name="day_schedule[]" value="Sabado"> <label for="checkboxSabado">Sábado</label><br>
                                                            <input type="checkbox" id="checkboxDomingo" name="day_schedule[]" value="Domingo"> <label for="checkboxDomingo">Domingo</label><br><br>
                                                        </td>
                                                        <td class="horario-manana">
                                                            <!-- Horario de mañana -->
                                                            <div class="selectores-manana">
                                                                <select name="horainicioman" class="input-text" fixed-select></select>
                                                                <span class="col-auto"> - </span>
                                                                <select name="horafinman" class="input-text fixed-select"></select>
                                                            </div>
                                                            
                                                        </td>

                                                        <td class="horario-tarde">
                                                            <!-- Horario de tarde -->
                                                            <div class="selectores-tarde">
                                                                <select name="horainiciotar" class="input-text fixed-select"></select>
                                                                <span class="col-auto"> - </span>
                                                                <select name="horafintar" class="input-text fixed-select"></select><br><br>
                                                            </div>
                                                            
                                                        </td>
                                                    </tr>
                                            </table>
                                            <div class="boton-centrado">
                                                <!-- Botón para agregar el horario -->
                                                <input type="submit" value="Agregar horario" class="login-btn btn-primary btn" name="shedulesubmit">
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            </table>
                    </div>
                    ';
                    
                    // Procesar los datos del formulario de horario personalizado
                    if (isset($_POST['submitPersonalizado'])) {
                        $doctor_id = $_GET['id'];  // ID del doctor obtenido de la URL
                        $dias = $_POST['dias'] ?? [];  // Array de días seleccionados

                        if (!empty($dias)) {
                            foreach ($dias as $dia) {
                                // Recoger horarios de mañana y tarde de cada día
                                $horainicioman = $_POST['horainicioman_' . $dia] ?? null;
                                $horafinman = $_POST['horafinman_' . $dia] ?? null;
                                $horainiciotar = $_POST['horainiciotar_' . $dia] ?? null;
                                $horafintar = $_POST['horafintar_' . $dia] ?? null;

                                // Insertar el horario solo si al menos un horario está definido (mañana o tarde)
                                if ($horainicioman || $horainiciotar) {
                                    $sql = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar)
                                            VALUES ('$doctor_id', '$dia', '$horainicioman', '$horafinman', '$horainiciotar', '$horafintar')";

                                    // Ejecutar la consulta
                                    if ($database->query($sql)) {
                                        $_SESSION['success_message'] = "Horario personalizado agregado correctamente para el día $dia.";
                                    } else {
                                        $_SESSION['error_message'] = "Error al agregar el horario personalizado para el día $dia: " . $database->error;
                                    }
                                } else {
                                    $_SESSION['error_message'] = "Debe ingresar al menos un horario para el día $dia.";
                                }
                            }

                            // Redirigir para evitar el reenvío del formulario
                            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $doctor_id);
                            exit();
                        } else {
                            $_SESSION['error_message'] = "Por favor seleccione al menos un día.";
                        }
                    }
                }

                ?>
                    </div>
                </div>

                <!-- Contenido de Horario Personalizado -->
                <div class="content_box">
                    <div class="content">
                            
                        <!-- Formulario de Horario Personalizado -->
                        <form id="horarioPersonalizadoForm" action="" method="POST">
                            <table class="horario-table">
                                <tr>
                                    <th>Día</th>
                                    <th>Horario de Mañana</th>
                                    <th>Horario de Tarde</th>
                                </tr>
                                <?php
                                /* $dias_semana = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"];
                                foreach ($dias_semana as $dia) {
                                    echo '<tr>';
                                    echo '<td><input type="checkbox" name="dias[]" value="'.$dia.'"> '.$dia.'</td>';
                                    echo '<td>Inicio: <input type="time" name="horainicioman_'.$dia.'"> Fin: <input type="time" name="horafinman_'.$dia.'"></td>';
                                    echo '<td>Inicio: <input type="time" name="horainiciotar_'.$dia.'"> Fin: <input type="time" name="horafintar_'.$dia.'"></td>';
                                    echo '</tr>';
                                } */
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="dias[]" value="Lunes"> Lunes
                                    </td>
                                    <td>
                                        <select name="horainicioman_Lunes" class="personalized-select" style="font-size: 8px;"></select> 
                                        <span>-</span>
                                        <select name="horafinman_Lunes" class="personalized-select" style="font-size: 8px"></select>
                                    </td>
                                    <td>
                                        <select name="horainiciotar_Lunes" class="personalized-select" style="font-size: 8px"></select> 
                                        <span>-</span>
                                        <select name="horafintar_Lunes" class="personalized-select" style="font-size: 8px"></select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="dias[]" value="Martes"> Martes
                                    </td>
                                    <td>
                                        <select name="horainicioman_Martes" class="personalized-select" style="font-size: 8px"></select> 
                                        <span>-</span>
                                        <select name="horafinman_Martes" class="personalized-select" style="font-size: 8px"></select>
                                    </td>
                                    <td>
                                        <select name="horainiciotar_Martes" class="personalized-select" style="font-size: 8px"></select> 
                                        <span>-</span>
                                        <select name="horafintar_Martes" class="personalized-select" style="font-size: 8px"></select>
                                    </td>
                                </tr>
                                <tr>
                                <td>
                                    <input type="checkbox" name="dias[]" value="Miércoles"> Miércoles
                                </td>
                                <td>
                                    <select name="horainicioman_Miércoles" class="personalized-select" style="font-size: 8px"></select> 
                                    <span>-</span>
                                    <select name="horafinman_Miércoles" class="personalized-select" style="font-size: 8px"></select>
                                </td>
                                <td>
                                    <select name="horainiciotar_Miércoles" class="personalized-select" style="font-size: 8px"></select> 
                                    <span>-</span>
                                    <select name="horafintar_Miércoles" class="personalized-select" style="font-size: 8px"></select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" name="dias[]" value="Jueves"> Jueves
                                </td>
                                <td>
                                    <select name="horainicioman_Jueves" class="personalized-select" style="font-size: 8px"></select>
                                    <span>-</span>
                                    <select name="horafinman_Jueves" class="personalized-select" style="font-size: 8px"></select>
                                </td>
                                <td>
                                    <select name="horainiciotar_Jueves" class="personalized-select" style="font-size: 8px"></select>
                                    <span>-</span> 
                                    <select name="horafintar_Jueves" class="personalized-select" style="font-size: 8px"></select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" name="dias[]" value="Viernes" > Viernes
                                </td>
                                <td>
                                    <select name="horainicioman_Viernes" class="personalized-select" style="font-size: 8px"></select> 
                                    <span>-</span>
                                    <select name="horafinman_Viernes" class="personalized-select" style="font-size: 8px"></select>
                                </td>
                                <td>
                                    <select name="horainiciotar_Viernes" class="personalized-select" style="font-size: 8px"></select> 
                                    <span>-</span>
                                    <select name="horafintar_Viernes" class="personalized-select" style="font-size: 8px"></select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" name="dias[]" value="Sábado"> Sábado
                                </td>
                                <td>
                                    <select name="horainicioman_Sábado" class="personalized-select" style="font-size: 8px"></select> 
                                    <span>-</span>
                                    <select name="horafinman_Sábado" class="personalized-select" style="font-size: 8px"></select>
                                </td>
                                <td>
                                    <select name="horainiciotar_Sábado" class="personalized-select" style="font-size: 8px"></select> 
                                    <span>-</span>
                                    <select name="horafintar_Sábado" class="personalized-select" style="font-size: 8px"></select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" name="dias[]" value="Domingo"> Domingo
                                </td>
                                <td>
                                    <select name="horainicioman_Domingo" class="personalized-select" style="font-size: 8px"></select> 
                                    <span>-</span>
                                    <select name="horafinman_Domingo" class="personalized-select" style="font-size: 8px"></select>
                                </td>
                                <td>
                                    <select name="horainiciotar_Domingo" class="personalized-select" style="font-size: 8px"></select> 
                                    <span>-</span>
                                    <select name="horafintar_Domingo" class="personalized-select" style="font-size: 8px"></select>
                                </td>
                            </tr>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                            </table>
                            
                            <div class="boton-centrado-personalizado">
                                <input type="submit" value="Agregar horario" name="submitPersonalizado">
                            </div>
                        </form>
                    </div>
                </div>
           </div>       

            <script>
                // Función para generar opciones de horarios en intervalos de 30 minutos
                function generarOpcionesHorario(selectElement, horaInicio, horaFin) {
                    const minutosIntervalo = 30;
                    const horaInicioArr = horaInicio.split(':');
                    const horaFinArr = horaFin.split(':');

                    const horaInicial = parseInt(horaInicioArr[0]);
                    const minutoInicial = parseInt(horaInicioArr[1]);
                    const horaFinal = parseInt(horaFinArr[0]);
                    const minutoFinal = parseInt(horaFinArr[1]);

                    selectElement.innerHTML = ''; // Limpiar opciones previas

                    // Agregar opción por defecto vacía y deshabilitada
                    const opcionDefault = new Option('Elija', '', true, true);
                    opcionDefault.disabled = true;
                    selectElement.add(opcionDefault);

                    for (let hora = horaInicial; hora <= horaFinal; hora++) {
                        for (let minuto = 0; minuto < 60; minuto += minutosIntervalo) {
                            if (hora === horaFinal && minuto >= minutoFinal) break;

                            const horaFormateada = (hora < 10 ? '0' : '') + hora + 'h' + (minuto < 10 ? '0' : '') + minuto;
                            const opcionTexto = `${horaFormateada}`;
                            const opcionValor = `${hora < 10 ? '0' : ''}${hora}:${minuto < 10 ? '0' : ''}${minuto}:00`;
                            const opcion = new Option(opcionTexto, opcionValor);
                            selectElement.add(opcion);
                        }
                    }
                }

                // Generar opciones de horario para formulario de horario fijo
                if (horarioFijoForm) {
                    const selectInicioManana = horarioFijoForm.querySelector('select[name="horainicioman"]');
                    const selectFinManana = horarioFijoForm.querySelector('select[name="horafinman"]');
                    const selectInicioTarde = horarioFijoForm.querySelector('select[name="horainiciotar"]');
                    const selectFinTarde = horarioFijoForm.querySelector('select[name="horafintar"]');

                    // Generar opciones de 07:00 a 12:00 para la mañana
                    generarOpcionesHorario(selectInicioManana, '07:00', '12:00');
                    generarOpcionesHorario(selectFinManana, '07:30', '12:30');

                    // Generar opciones de 13:00 a 18:00 para la tarde
                    generarOpcionesHorario(selectInicioTarde, '13:00', '18:00');
                    generarOpcionesHorario(selectFinTarde, '13:30', '18:30');
        }

                // Generar opciones de horario para formulario de horario personalizado
                if (horarioPersonalizadoForm) {
                    const diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

                    diasSemana.forEach(dia => {
                        const selectInicioManana = horarioPersonalizadoForm.querySelector(`select[name="horainicioman_${dia}"]`);
                        const selectFinManana = horarioPersonalizadoForm.querySelector(`select[name="horafinman_${dia}"]`);
                        const selectInicioTarde = horarioPersonalizadoForm.querySelector(`select[name="horainiciotar_${dia}"]`);
                        const selectFinTarde = horarioPersonalizadoForm.querySelector(`select[name="horafintar_${dia}"]`);

                        if (selectInicioManana && selectFinManana) {
                            generarOpcionesHorario(selectInicioManana, '07:00', '12:00');
                            generarOpcionesHorario(selectFinManana, '07:30', '12:30');
                        }

                        if (selectInicioTarde && selectFinTarde) {
                            generarOpcionesHorario(selectInicioTarde, '13:00', '18:00');
                            generarOpcionesHorario(selectFinTarde, '13:30', '18:30');
                        }
                    });
                }

               // Función para validar horarios fijos
                function validarHorarios(formulario) {
                    // Verificar si al menos un día está seleccionado
                    const diasSeleccionados = formulario.querySelectorAll('input[name="day_schedule[]"]:checked');
                    if (diasSeleccionados.length === 0) {
                        alert("Por favor, selecciona al menos un día.");
                        return false;
                    }

                    // Obtener los valores de los inputs
                    const horaInicioManana = formulario.querySelector('select[name="horainicioman"]').value;
                    const horaFinManana = formulario.querySelector('select[name="horafinman"]').value;
                    const horaInicioTarde = formulario.querySelector('select[name="horainiciotar"]').value;
                    const horaFinTarde = formulario.querySelector('select[name="horafintar"]').value;

                    console.log("Hora de inicio mañana:", horaInicioManana);
                    console.log("Hora de fin mañana:", horaFinManana);
                    console.log("Hora de inicio tarde:", horaInicioTarde);
                    console.log("Hora de fin tarde:", horaFinTarde);

                    // Validación para la mañana: si se selecciona una hora de inicio, también debe haber una hora de fin, y viceversa.
                    if (horaInicioManana && !horaFinManana) {
                        alert("Por favor, selecciona una hora de fin para la mañana.");
                        return false;
                    }
                    if (!horaInicioManana && horaFinManana) {
                        alert("Por favor, selecciona una hora de inicio para la mañana.");
                        return false;
                    }

                     // Validación para la tarde: si se selecciona una hora de inicio, también debe haber una hora de fin, y viceversa.
                    if (horaInicioTarde && !horaFinTarde) {
                        alert("Por favor, selecciona una hora de fin para la tarde.");
                        return false;
                    }
                    if (!horaInicioTarde && horaFinTarde) {
                        alert("Por favor, selecciona una hora de inicio para la tarde.");
                        return false;
                    }

                    // Mostrar valores en consola para depuración
                    console.log("Hora de inicio de mañana:", horaInicioManana);
                    console.log("Hora de fin de mañana:", horaFinManana);
                    console.log("Hora de inicio de tarde:", horaInicioTarde);
                    console.log("Hora de fin de tarde:", horaFinTarde);

                    

                    // Validación de la mañana: la hora de inicio debe ser anterior a la hora de fin
                    if (horaInicioManana && horaFinManana && horaInicioManana >= horaFinManana) {
                        alert("El horario de inicio de la mañana debe ser anterior al horario de fin de la mañana.");
                        return false;
                    }

                    // Validación de la tarde: la hora de inicio debe ser anterior a la hora de fin
                    if (horaInicioTarde && horaFinTarde && horaInicioTarde >= horaFinTarde) {
                        alert("El horario de inicio de la tarde debe ser anterior al horario de fin de la tarde.");
                        return false;
                    }

                    // Validar que al menos un horario esté ingresado (mañana o tarde)
                    if ((!horaInicioManana && !horaFinManana) && (!horaInicioTarde && !horaFinTarde)) {
                        alert("Por favor, ingresa al menos un horario válido para la mañana o la tarde.");
                        return false;
                    }

                    // Si todo está bien, retornamos true
                    console.log("Validación completada correctamente.");
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
                            alert(`El horario de inicio de la mañana debe ser anterior al horario de fin de la mañana para el día ${dia}.`);
                            valid = false;
                        }

                        // Validación de que la hora de inicio de la tarde sea anterior a la de fin
                        if (horaInicioTarde && horaFinTarde && horaInicioTarde >= horaFinTarde) {
                            alert(`El horario de inicio de la tarde debe ser anterior al horario de fin de la tarde para el día ${dia}.`);
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

                // Validación de formularios
                document.addEventListener("DOMContentLoaded", function() {
                    const horarioFijoForm = document.getElementById('horarioFijoForm');
                    const horarioPersonalizadoForm = document.getElementById('horarioPersonalizadoForm');

                    // Validación de Horario Fijo
                    if (horarioFijoForm) {
                        console.log("Formulario de horario fijo encontrado.");

                        horarioFijoForm.addEventListener('submit', function(event) {
                            console.log("Evento submit activado para horario fijo.");
                            if (!validarHorarios(horarioFijoForm)) {
                                console.log("Validación de horario fijo fallida.");
                                event.preventDefault(); 
                            } else {
                                console.log("Validación de horario fijo exitosa, formulario enviado.");
                            }
                        });
                    } else {
                        console.error("Formulario de horario fijo no encontrado.");
                    }

                    // Validación de Horario Personalizado
                    if (horarioPersonalizadoForm) {
                        console.log("Formulario de horario personalizado encontrado.");

                        horarioPersonalizadoForm.addEventListener('submit', function(event) {
                            console.log("Evento submit activado para horario personalizado.");
                            if (!validarHorariosPersonalizados(horarioPersonalizadoForm)) {
                                console.log("Validación de horario personalizado fallida.");
                                event.preventDefault(); 
                            } else {
                                console.log("Validación de horario personalizado exitosa, formulario enviado.");
                            }
                        });
                    } else {
                        console.error("Formulario de horario personalizado no encontrado.");
                    }

                    // Código para pestañas
                    const tabs = document.querySelectorAll('.tab_btn');
                    const all_content = document.querySelectorAll('.content_box');

                    tabs.forEach((tab, index) => {
                        tab.addEventListener('click', (e) => { 
                            tabs.forEach(tab => { tab.classList.remove('active') });
                            tab.classList.add('active');
                            var line = document.querySelector('.line');
                            line.style.width = e.target.offsetWidth + "px"; 
                            line.style.left = e.target.offsetLeft + "px"; 
                            all_content.forEach(content => { content.classList.remove('active') });
                            all_content[index].classList.add('active');
                        })
                    });

                    // Mostrar pestaña por defecto
                    tabs[0].click(); 
                });
            </script> 
</body>
</html>