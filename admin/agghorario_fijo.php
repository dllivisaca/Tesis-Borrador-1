<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href="../css/animations.css">   
    <link rel="stylesheet" href="../css/main.css">      
    <link rel="stylesheet" href="../css/admin.css"> -->
    <link rel="stylesheet" href="../css/styles.css"> 
        
    <title>Horarios</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        /* Estilos para las pestañas */
        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
        }

        /* Botones de las pestañas */
        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
        }

        /* Cambiar el color del botón de la pestaña al pasar el mouse */
        .tab button:hover {
            background-color: #ddd;
        }

        /* Color de la pestaña activa */
        .tab button.active {
            background-color: #ccc;
        }

        /* Estilo de contenido de pestañas */
        .tabcontent {
            /* display: none; */
            padding: 16px;
            border: 1px solid #ccc;
            border-top: none;
        }
         /* Mostrar la primera pestaña por defecto */
         .tabcontent.active {
            display: block;
        }
</style>
</head>
<body>
    <?php

    //learn from w3schools.com

    session_start();

    // Mostrar mensajes de éxito o error si existen
    if (isset($_SESSION['success_message'])) {
        echo '<p style="color: green;">' . $_SESSION['success_message'] . '</p>';
        unset($_SESSION['success_message']); // Eliminar el mensaje para evitar que se muestre nuevamente
    }

    if (isset($_SESSION['error_message'])) {
        echo '<p style="color: red;">' . $_SESSION['error_message'] . '</p>';
        unset($_SESSION['error_message']); // Eliminar el mensaje para evitar que se muestre nuevamente
    }


    if(isset($_SESSION["usuario"])){
        if(($_SESSION["usuario"])=="" or $_SESSION['usuario_rol']!='adm'){
            header("location: ../login.php");
            exit;
        }
    }else{
        header("location: ../login.php");
        exit;
    }

    //import database
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
                        $sql = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar)
                                VALUES ('$doctor_id', '$day', '$horainicioman', '$horafinman', '$horainiciotar', '$horafintar')";
            
                        if ($database->query($sql)) {
                            $_SESSION['success_message'] = "Horario para el día $day agregado correctamente.";
                        } else {
                            $_SESSION['error_message'] = "Error al agregar el horario para el día $day: " . $database->error;
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

                            $sql = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar)
                                    VALUES ('$doctor_id', '$dia', '$horainicioman', '$horafinman', '$horainiciotar', '$horafintar')";

                            if ($database->query($sql)) {
                                $_SESSION['success_message'] = "Horario personalizado para el día $dia agregado correctamente.";
                            } else {
                                $_SESSION['error_message'] = "Error al agregar el horario personalizado para el día $dia: " . $database->error;
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
                                    <!-- <p class="profile-subtitle">admin@edoc.com</p> -->
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
                <!-- <tr class="menu-row" >
                    <td class="menu-btn menu-icon-dashbord" >
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></a></div></a>
                    </td>
                </tr> -->
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor ">
                        <a href="doctores.php" class="non-style-link-menu "><div><p class="menu-text">Doctores</p></a></div>
                    </td>
                </tr>
                
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-patient">
                        <a href="pacientes.php" class="non-style-link-menu"><div><p class="menu-text">Pacientes</p></a></div>
                    </td>
                </tr>

                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment menu-active menu-icon-appoinment-active">
                        <a href="citas.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Citas agendadas</p></a></div>
                    </td>
                </tr>

                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-schedule ">
                        <a href="horarios.php" class="non-style-link-menu"><div><p class="menu-text">Horarios disponibles</p></div></a>
                    </td>
                </tr>

            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                    <a href="horarios.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Agregar Horario</p>
                                           
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php 

                        date_default_timezone_set('Asia/Kolkata');

                        $today = date('Y-m-d');
                        echo $today;

                        $list110 = $database->query("select  * from  horarios;");

                        ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>


                </tr>
        <!-- <div class="tab_box">
            <button class="tab_btn">Horario fijo</button>
            <button class="tab_btn">Horario personalizado</button>
            <div class="line"></div>
        </div>  -->
                    
                    
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
            


            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2></h2>
                        
                        
                        <div style="display: flex;justify-content: center;">
                        <table width="60%" class="sub-table scrolldown add-doc-form-container" border="0">
                            
                            <tr>

                                <td class="label-td" colspan="2">
                                    <label for="espec" class="form-label">Especialidad: </label>
                                </td>
                                <td class="label-td" colspan="2">
                                    '.$especial_name.'<br><br>
                                </td>
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Doctor: </label>
                                </td>
                                <td class="label-td" colspan="2">
                                    '.$docnombre.'<br><br>
                                </td>

                            </tr>

                        </table>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>

            
            ';
        }
    
            ?>
            

            <!-- Contenedor de las pestañas -->
            <!-- <div class="tab">
                <button class="tablinks" onclick="openTab(event, 'HorarioFijo')" id="defaultOpen">Horario Fijo</button>
                <button class="tablinks" onclick="openTab(event, 'HorarioPersonalizado')" >Horario Personalizado</button>
            </div> -->

            <div class="tab_box">
                <button class="tab_btn">Horario fijo</button>
                <button class="tab_btn">Horario personalizado</button>
                <div class="line"></div>
            </div> 
          
            
            <!-- Contenido de Horario Fijo -->
            <div class="content_box">
                <div class="content">
                    <h3>Horario Fijo</h3>
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
                <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        
                        <div style="display: flex;justify-content: center;">
                            <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                                
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <form id="horarioFijoForm" action="" method="POST" class="add-new-form">
                                            <!-- Días de la semana con checkboxes -->
                                        <input type="checkbox" id="checkboxLunes" name="day_schedule[]" value="Lunes"> <label for="checkboxLunes">Lunes</label><br>
                                        <input type="checkbox" id="checkboxMartes" name="day_schedule[]" value="Martes"> <label for="checkboxMartes">Martes</label><br>
                                        <input type="checkbox" id="checkboxMiercoles" name="day_schedule[]" value="Miercoles"> <label for="checkboxMiercoles">Miércoles</label><br>
                                        <input type="checkbox" id="checkboxJueves" name="day_schedule[]" value="Jueves"> <label for="checkboxJueves">Jueves</label><br>
                                        <input type="checkbox" id="checkboxViernes" name="day_schedule[]" value="Viernes"> <label for="checkboxViernes">Viernes</label><br>
                                        <input type="checkbox" id="checkboxSabado" name="day_schedule[]" value="Sabado"> <label for="checkboxSabado">Sábado</label><br>
                                        <input type="checkbox" id="checkboxDomingo" name="day_schedule[]" value="Domingo"> <label for="checkboxDomingo">Domingo</label><br><br>

                                        <!-- Horario de mañana -->
                                        <label for="horainicioman" class="form-label">Horario de mañana: </label>
                                        <select name="horainicioman" class="input-text"></select>
                                        <span class="col-auto"> - </span>
                                        <select name="horafinman" class="input-text"></select><br><br>

                                        <!-- Horario de tarde -->
                                        <label for="horainiciotar" class="form-label">Horario de tarde: </label>
                                        <select name="horainiciotar" class="input-text"></select>
                                        <span class="col-auto"> - </span>
                                        <select name="horafintar" class="input-text"></select><br><br>

                                        <!-- Botón para agregar el horario -->
                                        <input type="submit" value="Agregar horario" class="login-btn btn-primary btn" name="shedulesubmit">
                                        </form>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </center>
                    <br><br>
                </div>
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

                <div class="content">
                    <h3>Horario Personalizado</h3>
            
                
                <!-- Formulario de Horario Personalizado -->
                <form id="horarioPersonalizadoForm" action="" method="POST">
                    <table border="0" width="100%">
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
                                Inicio: <select name="horainicioman_Lunes"></select> 
                                Fin: <select name="horafinman_Lunes"></select>
                            </td>
                            <td>
                                Inicio: <select name="horainiciotar_Lunes"></select> 
                                Fin: <select name="horafintar_Lunes"></select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="dias[]" value="Martes"> Martes
                            </td>
                            <td>
                                Inicio: <select name="horainicioman_Martes"></select> 
                                Fin: <select name="horafinman_Martes"></select>
                            </td>
                            <td>
                                Inicio: <select name="horainiciotar_Martes"></select> 
                                Fin: <select name="horafintar_Martes"></select>
                            </td>
                        </tr>
                        <tr>
                        <td>
                            <input type="checkbox" name="dias[]" value="Miércoles"> Miércoles
                        </td>
                        <td>
                            Inicio: <select name="horainicioman_Miércoles"></select> 
                            Fin: <select name="horafinman_Miércoles"></select>
                        </td>
                        <td>
                            Inicio: <select name="horainiciotar_Miércoles"></select> 
                            Fin: <select name="horafintar_Miércoles"></select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" name="dias[]" value="Jueves"> Jueves
                        </td>
                        <td>
                            Inicio: <select name="horainicioman_Jueves"></select> 
                            Fin: <select name="horafinman_Jueves"></select>
                        </td>
                        <td>
                            Inicio: <select name="horainiciotar_Jueves"></select> 
                            Fin: <select name="horafintar_Jueves"></select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" name="dias[]" value="Viernes"> Viernes
                        </td>
                        <td>
                            Inicio: <select name="horainicioman_Viernes"></select> 
                            Fin: <select name="horafinman_Viernes"></select>
                        </td>
                        <td>
                            Inicio: <select name="horainiciotar_Viernes"></select> 
                            Fin: <select name="horafintar_Viernes"></select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" name="dias[]" value="Sábado"> Sábado
                        </td>
                        <td>
                            Inicio: <select name="horainicioman_Sábado"></select> 
                            Fin: <select name="horafinman_Sábado"></select>
                        </td>
                        <td>
                            Inicio: <select name="horainiciotar_Sábado"></select> 
                            Fin: <select name="horafintar_Sábado"></select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" name="dias[]" value="Domingo"> Domingo
                        </td>
                        <td>
                            Inicio: <select name="horainicioman_Domingo"></select> 
                            Fin: <select name="horafinman_Domingo"></select>
                        </td>
                        <td>
                            Inicio: <select name="horainiciotar_Domingo"></select> 
                            Fin: <select name="horafintar_Domingo"></select>
                        </td>
                    </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                    </table>
                    <input type="submit" value="Agregar horario" name="submitPersonalizado">
                </form>
            </div>
            </div>
            <!-- <script>
                
                // Función para abrir pestañas
                function openTab(evt, tabName) {
                    var i, tabcontent, tablinks;

                    // Ocultar todas las pestañas
                    tabcontent = document.getElementsByClassName("tabcontent");
                    for (i = 0; i < tabcontent.length; i++) {
                        tabcontent[i].style.display = "none";  // Ocultar todas las pestañas
                    }

                    // Remover la clase "active" de todos los botones
                    tablinks = document.getElementsByClassName("tablinks");
                    for (i = 0; i < tablinks.length; i++) {
                        tablinks[i].className = tablinks[i].className.replace(" active", "");  // Remover "active"
                    }

                    // Mostrar la pestaña seleccionada
                    document.getElementById(tabName).style.display = "block";  // Mostrar la pestaña
                    evt.currentTarget.className += " active";  // Agregar la clase "active" al botón actual
                }

                // Ejecutar esta función cuando el DOM esté completamente cargado
                document.addEventListener("DOMContentLoaded", function() {
                    // Abrir la pestaña predeterminada (Horario Fijo) al cargar la página
                    document.getElementById("defaultOpen").click();
                });
            </script> -->

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
                    const opcionDefault = new Option('Seleccione', '', true, true);
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
                    const all_content = document.querySelectorAll('.content');

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