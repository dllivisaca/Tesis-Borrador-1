<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/paciente/horarios.css">
        
    <title>Sessions</title>
    <!-- <style>
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
    </style> -->
</head>
<body>
    <?php
    error_reporting(E_ERROR | E_PARSE);

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
            <div class="profile-container">
                <img src="../img/logo.png" alt="Logo" class="menu-logo">
                
                <p class="profile-title"><?php echo substr($username,0,13)  ?>..</p>
                
            </div>


            <a href="../logout.php"><button class="btn-logout">Cerrar sesión</button></a>
            <div class="linea-separadora"></div>
            <div class="menu-links">
                <a href="citas.php" class="menu-link">Citas agendadas</a>
                <a href="horarios.php" class="menu-link menu-link-active">Horarios disponibles</a>
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
                Horarios disponibles
                </p>
            </div>
        </div>

        <div class="filter-row">
            <form method="POST" action="horarios.php">
                <label for="docid" class="label-doctor">Doctor:</label>
                <select name="docid" id="docid" class="box filter-container-items">
                    <option value="" disabled selected hidden>Escoge un doctor de la lista</option>
                    <?php 
                        $list11 = $database->query("select * from doctor order by docnombre asc;");
                        while ($row = $list11->fetch_assoc()) {
                            echo "<option value='".$row["docid"]."'>".$row["docnombre"]."</option>";
                        }
                    ?>
                </select>
                <button type="submit" class="btn-primary-soft btn button-icon btn-filter">Buscar</button>
            </form>
        </div>


 
        <?php
                /* $sqlmain= "SELECT doctor.docid, doctor.docnombre, especialidades.espnombre, disponibilidad_doctor.dia_semana, disponibilidad_doctor.horainicioman, disponibilidad_doctor.horafinman, disponibilidad_doctor.horainiciotar, disponibilidad_doctor.horafintar FROM doctor LEFT JOIN disponibilidad_doctor ON doctor.docid = disponibilidad_doctor.docid LEFT JOIN especialidades ON doctor.especialidades = especialidades.id ORDER BY doctor.docnombre, disponibilidad_doctor.dia_semana"; */
                
                $sqlmain= "SELECT doctor.docid, doctor.docnombre, especialidades.espnombre, especialidades.id as especialidad_id, disponibilidad_doctor.dia_semana, disponibilidad_doctor.horainicioman, disponibilidad_doctor.horafinman, disponibilidad_doctor.horainiciotar, disponibilidad_doctor.horafintar FROM doctor LEFT JOIN disponibilidad_doctor ON doctor.docid = disponibilidad_doctor.docid LEFT JOIN especialidades ON doctor.especialidades = especialidades.id ORDER BY doctor.docnombre, disponibilidad_doctor.dia_semana";

                $result= $database->query($sqlmain);
        ?>
        
                <tr>
                    <td colspan="4">
                        <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                            <thead>
                                <tr>
                                    <th class="table-headin">Doctor</th>
                                    <th class="table-headin">Especialidad</th>
                                    <th class="table-headin">Horarios Disponibles</th>
                                    <th class="table-headin">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                if ($result->num_rows > 0) {
                                    $current_doctor = null;
                                    $current_horarios = [];
                                    
                                    while ($row = $result->fetch_assoc()) {
                                        $docid = $row['docid'];
                                        $especialidad_id = $row['especialidad_id'];
                                        $docnombre = $row['docnombre'];
                                        $espnombre = $row['espnombre'];
                                        $dia_semana = $row['dia_semana'];
                                        
                                        $horainicioman = ($row['horainicioman'] != '00:00:00') ? substr($row['horainicioman'], 0, 5) : '';
                                        $horafinman = ($row['horafinman'] != '00:00:00') ? substr($row['horafinman'], 0, 5) : '';
                                        $horainiciotar = ($row['horainiciotar'] != '00:00:00') ? substr($row['horainiciotar'], 0, 5) : '';
                                        $horafintar = ($row['horafintar'] != '00:00:00') ? substr($row['horafintar'], 0, 5) : '';
                                        
                                        // Si cambia el doctor o es el primer doctor, procesa el anterior
                                        if ($current_doctor != $docid) {
                                            if ($current_doctor !== null) {
                                                // Mostrar la fila del doctor anterior
                                                echo '<tr>';
                                                echo '<td>' . $current_docnombre . '</td>';
                                                echo '<td>' . $current_espnombre . '</td>';
                                                echo '<td>';
                                                if (!empty($current_horarios)) {
                                                    echo '<div class="horarios-grid">';
                                                    foreach ($current_horarios as $dia => $horarios) {
                                                        echo '<div><b>' . $dia . '</b><br>' . implode('<br>', $horarios) . '</div>';
                                                    }
                                                    echo '</div>';
                                                } else {
                                                    echo '<p>No se encontraron horarios disponibles</p>'; // Mensaje claro
                                                }
                                                echo '</td>';
                                                echo '<td>';
                                                if (!empty($current_horarios)) {
                                                    echo '<button class="btn-primary-soft btn">Agendar cita</button>';
                                                } else {
                                                    echo '<button class="btn-primary-soft btn" disabled>Sin horarios</button>';
                                                }
                                                echo '</tr>';
                                            }
                                            
                                            // Reinicia variables para el nuevo doctor
                                            $current_doctor = $docid;
                                            $current_docnombre = $docnombre;
                                            $current_espnombre = $espnombre;
                                            $current_horarios = [];
                                        }
                                        
                                        // Añade los horarios para el doctor actual, si existen
                                        if (!empty($dia_semana)) { // Verifica que haya un día válido
                                            if (!isset($current_horarios[$dia_semana])) {
                                                $current_horarios[$dia_semana] = [];
                                            }
                                            if ($horainicioman && $horafinman) {
                                                $current_horarios[$dia_semana][] = $horainicioman . ' - ' . $horafinman;
                                            }
                                            if ($horainiciotar && $horafintar) {
                                                $current_horarios[$dia_semana][] = $horainiciotar . ' - ' . $horafintar;
                                            }
                                        }
                                    }
                                    
                                    // Procesa el último doctor fuera del ciclo
                                    if ($current_doctor !== null) {
                                        echo '<tr>';
                                        echo '<td>' . $current_docnombre . '</td>';
                                        echo '<td>' . $current_espnombre . '</td>';
                                        echo '<td>';
                                        if (!empty($current_horarios)) {
                                            echo '<div class="horarios-grid">';
                                            foreach ($current_horarios as $dia => $horarios) {
                                                echo '<div><b>' . $dia . '</b><br>' . implode('<br>', $horarios) . '</div>';
                                            }
                                            echo '</div>';
                                        } else {
                                            echo '<p>No se encontraron horarios disponibles</p>';
                                        }
                                        echo '</td>';
                                        echo '<td>';
                                        if (!empty($current_horarios)) {
                                            echo '<button class="btn-primary-soft btn">Agendar cita</button>';
                                        } else {
                                            echo '<button class="btn-primary-soft btn" disabled></button>';
                                        }
                                        echo '</tr>';
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
                var maxDate = new Date(now.getTime() + 31 * 24 * 60 * 60 * 1000);

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
                document.getElementById("agendarBtn").disabled = true; // Deshabilitar botón inicialmente

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
                            document.getElementById("agendarBtn").disabled = false; // Habilitar el botón si hay horarios disponibles
                        } else {
                            document.getElementById("horas").innerHTML = '<option value="" disabled selected>No hay horarios disponibles para la fecha seleccionada</option>';
                            //document.getElementById("agendarBtn").disabled = true;
                            document.getElementById("agendarBtn").disabled = true; // Deshabilitar el botón si no hay horarios disponibles
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