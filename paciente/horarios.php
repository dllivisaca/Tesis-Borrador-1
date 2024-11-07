<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Sessions</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .horario-col {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .horario-item {
            width: 48%;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 40%;
            border-radius: 10px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php

    session_start();

    if(isset($_SESSION["usuario"])){
        if(($_SESSION["usuario"]=="" or $_SESSION['usuario_rol']!='pac')){
            header("location: ../login.php");
        }else {
            $usuario=$_SESSION["usuario"];
        }
    }else{
        header("location: ../login.php");
    }
    
    include("../conexion_db.php");
    $userrow = $database->query("select * from paciente where pacusuario='$usuario'");
    $userfetch=$userrow->fetch_assoc();
    $userid= $userfetch["pacid"];
    $username=$userfetch["pacnombre"];
    
    date_default_timezone_set('America/Guayaquil');
    $today = date('Y-m-d');
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
                                 <p class="profile-title"><?php echo substr($username,0,13)  ?>..</p>
                                 <p class="profile-subtitle"><?php echo substr($usuario,0,22)  ?></p>
                             </td>
                         </tr>
                         <tr>
                             <td colspan="2">
                                 <a href="../logout.php" ><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                             </td>
                         </tr>
                 </table>
                 </td>
             </tr>
             <tr class="menu-row" >
                    <td class="menu-btn menu-icon-home " >
                        <a href="citas.php" class="non-style-link-menu "><div><p class="menu-text">Inicio</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-session menu-active menu-icon-session-active">
                        <a href="horarios.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Horarios disponibles</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="citas.php" class="non-style-link-menu"><div><p class="menu-text">Mis citas agendadas</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="configuracion.php" class="non-style-link-menu"><div><p class="menu-text">Configuración</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
                /* $sqlmain= "SELECT doctor.docid, doctor.docnombre, especialidades.espnombre, disponibilidad_doctor.dia_semana, disponibilidad_doctor.horainicioman, disponibilidad_doctor.horafinman, disponibilidad_doctor.horainiciotar, disponibilidad_doctor.horafintar FROM doctor LEFT JOIN disponibilidad_doctor ON doctor.docid = disponibilidad_doctor.docid LEFT JOIN especialidades ON doctor.especialidades = especialidades.id ORDER BY doctor.docnombre, disponibilidad_doctor.dia_semana"; */
                
                $sqlmain= "SELECT doctor.docid, doctor.docnombre, especialidades.espnombre, especialidades.id as especialidad_id, disponibilidad_doctor.dia_semana, disponibilidad_doctor.horainicioman, disponibilidad_doctor.horafinman, disponibilidad_doctor.horainiciotar, disponibilidad_doctor.horafintar FROM doctor LEFT JOIN disponibilidad_doctor ON doctor.docid = disponibilidad_doctor.docid LEFT JOIN especialidades ON doctor.especialidades = especialidades.id ORDER BY doctor.docnombre, disponibilidad_doctor.dia_semana";

                $result= $database->query($sqlmain);
        ?>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                    <a href="horarios.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td >
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Horarios Disponibles</p>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php echo $today; ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:10px;width: 100%;" >
                        <center>
                        <div class="abc scroll">
                        <table width="90%" class="sub-table scrolldown" border="0" style="padding: 50px;border:none">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Especialidad</th>
                                    <th>Horarios Disponibles</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                if($result->num_rows == 0){
                                    echo '<tr>
                                    <td colspan="4">
                                    <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No se encontraron horarios disponibles!</p>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                }
                                else{
                                    $current_doctor = null;
                                    $horario_col_empty = true;

                                    //$current_especialidad = '';
                                    while($row = $result->fetch_assoc()){
                                        
                                        $docid = $row['docid'];
                                        $especialidad_id = $row['especialidad_id'];
                                        $docnombre = $row['docnombre'];
                                        $espnombre = $row['espnombre'];
                                        $dia_semana = $row['dia_semana'];
                                        $horainicioman = ($row['horainicioman'] != '00:00:00') ? substr($row['horainicioman'], 0, 5) : '';
                                        $horafinman = ($row['horafinman'] != '00:00:00') ? substr($row['horafinman'], 0, 5) : '';
                                        $horainiciotar = ($row['horainiciotar'] != '00:00:00') ? substr($row['horainiciotar'], 0, 5) : '';
                                        $horafintar = ($row['horafintar'] != '00:00:00') ? substr($row['horafintar'], 0, 5) : '';

                                        // Imprimir los datos obtenidos para asegurarte de que son correctos
                                        /* echo "<div>";
                                        echo "<p>Doctor ID: $docid - Especialidad ID: $especialidad_id</p>";
                                        echo "<p>$dia_semana: "; */

                                        // Cada vez que cambiamos de doctor, cerramos la fila anterior
                                        if ($current_doctor != $docid) {
                                            // Si es diferente del actual doctor, cerramos el bloque de doctor anterior si no es la primera iteración
                                            if ($current_doctor !== null) {
                                                if ($horario_col_empty) {
                                                    echo '<p>No se encontraron horarios disponibles</p>';
                                                }
                                                echo '</div></td>';
                                                echo '<td>';
                                                if (!$horario_col_empty) {
                                                    // Generar botón de agendamiento con los datos correctos
                                                    echo '<button class="login-btn btn-primary-soft btn agendar-btn" 
                                                                style="padding-top:11px;padding-bottom:11px;width:100%" 
                                                                data-docid="' . $current_docid . '" 
                                                                data-docnombre="' . $current_docnombre . '" 
                                                                data-espnombre="' . $current_espnombre . '" 
                                                                data-especialidad-id="' . $current_especialidad_id . '">Agendar cita</button>';
                                                }
                                                echo '</td></tr>';
                                            }

                                            // Actualizar el doctor actual y sus datos
                                            $current_doctor = $docid;
                                            $current_docid = $docid;
                                            $current_docnombre = $docnombre;
                                            $current_espnombre = $espnombre;
                                            $current_especialidad_id = $especialidad_id;

                                            $horario_col_empty = true;

                                            // Generar la fila HTML para el nuevo doctor
                                            echo '<tr>
                                                    <td>' . $docnombre . '</td>
                                                    <td>' . $espnombre . '</td>
                                                    <td>
                                                        <div class="horario-col">';
                                        }

                                        // Mostrar horarios de la mañana y la tarde
                                        if ($horainicioman != '' && $horafinman != '') {
                                            echo '<div class="horario-item">
                                                    <b>' . $dia_semana . '</b><br>
                                                    ' . $horainicioman . ' - ' . $horafinman . '<br>
                                                </div>';
                                            $horario_col_empty = false;
                                        }

                                        if ($horainiciotar != '' && $horafintar != '') {
                                            echo '<div class="horario-item">
                                                    <b>' . $dia_semana . '</b><br>
                                                    ' . $horainiciotar . ' - ' . $horafintar . '
                                                </div>';
                                            $horario_col_empty = false;
                                        }
                                    }

                                    // Cerrar el último doctor después de salir del ciclo
                                    if ($current_doctor !== null) {
                                        if ($horario_col_empty) {
                                            echo '<p>No se encontraron horarios disponibles</p>';
                                        }
                                        echo '</div></td>';
                                        echo '<td>';
                                        if (!$horario_col_empty) {
                                            // Generar botón de agendamiento con los datos correctos
                                            echo '<button class="login-btn btn-primary-soft btn agendar-btn" 
                                                        style="padding-top:11px;padding-bottom:11px;width:100%" 
                                                        data-docid="' . $current_docid . '" 
                                                        data-docnombre="' . $current_docnombre . '" 
                                                        data-espnombre="' . $current_espnombre . '" 
                                                        data-especialidad-id="' . $current_especialidad_id . '">Agendar cita</button>';
                                        }
                                        echo '</td></tr>';
                                    }
                                }

                            ?>
                            </tbody>
                        </table>
                        </div>
                        </center>
                    </td> 
                </tr>
            </table>
        </div>
    </div>

    <!-- Modal for scheduling an appointment -->
    <div id="agendarModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Agendar cita</h2>
            <p>Especialidad médica: <span id="modalEspnombre"></span></p>
            <p>Nombre del doctor: <span id="modalDocnombre"></span></p>
            <form id="agendarForm">
                <input type="hidden" id="pacid" name="pacid" value="<?php echo $userid; ?>">
                <input type="hidden" id="docid" name="docid">
                <input type="hidden" id="especialidad_id" name="especialidad_id">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required><br><br>
                <label for="horas">Horas disponibles:</label>
                <select id="horas" name="hora_inicio" required>
                    <option value="" disabled selected>Escoge una hora de la lista</option>
                </select><br><br>
                <button type="submit" class="login-btn btn-primary-soft btn" id="agendarBtn">+ Agendar cita</button>
                
            </form>
        </div>
    </div>

    <script>
        // Get modal element
        var modal = document.getElementById("agendarModal");
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Add click event to all "Agendar cita" buttons
        var agendarButtons = document.getElementsByClassName("agendar-btn");
        for (var i = 0; i < agendarButtons.length; i++) {
            agendarButtons[i].onclick = function() {
                var docid = this.getAttribute("data-docid");
                var docnombre = this.getAttribute("data-docnombre");
                var espnombre = this.getAttribute("data-espnombre");
                var especialidad_id = this.getAttribute("data-especialidad-id");

                /* console.log("DocID seleccionado: ", docid);
                console.log("EspecialidadID seleccionado: ", especialidad_id); */

                // Asignar los valores al formulario del modal
                
                document.getElementById("docid").value = docid; // Asignar el valor al input oculto
                document.getElementById("especialidad_id").value = especialidad_id; // Asignar el valor al input oculto
                document.getElementById("modalDocnombre").innerText = docnombre;
                document.getElementById("modalEspnombre").innerText = espnombre;

                // Set min and max dates for the date input
                var fechaInput = document.getElementById("fecha");
                var now = new Date();
                var minDate = new Date(now.getTime() + 24 * 60 * 60 * 1000); // 24 hours from now

                // Calculate max date (30 days from now)
                var maxDate = new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000);

                 // Convert minDate and maxDate to CST timezone
                /* var cstOffset = -6 * 60; // CST is UTC-6 in minutes
                minDate = new Date(minDate.getTime() + (minDate.getTimezoneOffset() + cstOffset) * 60000);
                maxDate = new Date(maxDate.getTime() + (maxDate.getTimezoneOffset() + cstOffset) * 60000); */

                // Formatear la fecha a 'yyyy-mm-dd' en zona horaria local
                function formatDate(date) {
                    var year = date.getFullYear();
                    var month = ('0' + (date.getMonth() + 1)).slice(-2);
                    var day = ('0' + date.getDate()).slice(-2);
                    return year + '-' + month + '-' + day;
                }

                // Format date to yyyy-mm-dd for min and max attributes
                /* var minDateStr = minDate.toISOString().split('T')[0];
                var maxDateStr = maxDate.toISOString().split('T')[0]; */
                var minDateStr = formatDate(minDate);
                var maxDateStr = formatDate(maxDate);


                // Set min and max attributes
                fechaInput.setAttribute("min", minDateStr);
                fechaInput.setAttribute("max", maxDateStr);

                // Show the modal
                modal.style.display = "block";
            }
        }
        // Obtener las horas disponibles al seleccionar la fecha
        document.getElementById("fecha").addEventListener("change", function() {
            var fecha = this.value;
            var docnombre = document.getElementById("modalDocnombre").innerText;

            // Verificar que se seleccionó una fecha y hay un doctor
            if (fecha && docnombre) {
                // Crear una petición AJAX para obtener los horarios
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "fetch_horarios.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var response = xhr.responseText;

                        // Crear un contenedor temporal para manipular el HTML recibido
                        var tempContainer = document.createElement('div');
                        tempContainer.innerHTML = '<select>' + response + '</select>';
                        var options = tempContainer.querySelectorAll('option');

                        var now = new Date();
                        var time24HoursFromNow = new Date(now.getTime() + 24 * 60 * 60 * 1000);

                        // Filtrar las opciones que son al menos 24 horas después
                        var filteredOptions = Array.from(options).filter(function(option) {
                            var time = option.value; // Suponiendo que el valor es la hora en formato 'HH:MM - HH:MM'

                            // Obtener la hora de inicio
                            var timeMatch = time.match(/^(\d{2}:\d{2})/);
                            if (timeMatch) {
                                var timeStart = timeMatch[1];
                                var dateParts = fecha.split('-');
                                var timeParts = timeStart.split(':');
                                var optionDateTime = new Date(dateParts[0], dateParts[1]-1, dateParts[2], timeParts[0], timeParts[1]);

                                return optionDateTime.getTime() >= time24HoursFromNow.getTime();
                            }
                            return false;
                        });

                        // Verificar si hay horarios disponibles
                        if (filteredOptions.length > 0) {
                            var optionsHTML = filteredOptions.map(function(option) {
                                return '<option value="' + option.value + '">' + option.text + '</option>';
                            }).join('');
                            document.getElementById("horas").innerHTML = optionsHTML;
                        } else {
                            document.getElementById("horas").innerHTML = '<option value="" disabled selected>No hay horarios disponibles para la fecha seleccionada</option>';
                            document.getElementById("agendarBtn").disabled = true;
                        }
                    }
                };
                xhr.send("fecha=" + fecha + "&docnombre=" + encodeURIComponent(docnombre));
            }
        });
        
        // Manejar el evento de agendar cita
        document.getElementById("agendarForm").addEventListener("submit", function(e) {
            e.preventDefault(); // Evitar que el formulario se envíe de forma predeterminada

            console.log("DocID enviado: ", document.getElementById("docid").value);
            console.log("EspecialidadID enviado: ", document.getElementById("especialidad_id").value);

            var formData = new FormData(this);
            formData.append("hora_fin", document.getElementById("horas").value);

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "agendar_cita.php", true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert(xhr.responseText);  // Mostrar mensaje de éxito o error
                    modal.style.display = "none";  // Cerrar el modal
                    location.reload(); // Recargar la página para actualizar el estado de los horarios
                }
            };
            xhr.send(formData);
        });
    </script>
</body>
</html>