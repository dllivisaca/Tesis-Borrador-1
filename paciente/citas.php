<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    
    <title>Mis citas agendadas</title>
    <style>
        .container {
            display: flex;
        }
        .menu {
            width: 20%;
            background-color: #f4f4f4;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .dash-body {
            width: 80%;
            padding: 20px;
        }
        .table-container {
            margin-top: 20px;
            width: 100%;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #f4f4f4;
        }
        .btn-cancel, .btn-edit {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .btn-cancel:hover, .btn-edit:hover {
            background-color: #0056b3;
        }
        .filter-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }
        .filter-container input[type="date"],
        .filter-container select,
        .filter-container button {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .logout-btn {
            background-color: #d9534f;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .logout-btn:hover {
            background-color: #c9302c;
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
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 40%;
            border-radius: 10px;
            position: relative;
            animation: transitionIn-Y-bottom 0.5s;
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

    if (isset($_SESSION["usuario"])) {
        if ($_SESSION["usuario"] == "" || $_SESSION['usuario_rol'] != 'pac') {
            header("location: ../login.php");
        } else {
            $usuario = $_SESSION["usuario"];
        }
    } else {
        header("location: ../login.php");
    }

    // Importar la base de datos
    include("../conexion_db.php");
    $userrow = $database->query("SELECT * FROM paciente WHERE pacusuario='$usuario'");
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch["pacid"];
    $username = $userfetch["pacnombre"];

    if (isset($_GET['action']) && $_GET['action'] == 'drop') {
        if (isset($_GET['id'])) {
            $citaid = intval($_GET['id']);
    
            // Verificar que la cita pertenece al usuario actual
            $citaQuery = $database->prepare("SELECT * FROM citas WHERE citaid = ? AND pacid = ?");
            $citaQuery->bind_param("ii", $citaid, $userid);
            $citaQuery->execute();
            $citaResult = $citaQuery->get_result();
    
            if ($citaResult->num_rows > 0) {
                // Mostrar un modal de confirmación
                echo '
                <div id="cancelarModal" class="modal" style="display:block;">
                    <div class="modal-content">
                        <span class="close" onclick="document.getElementById(\'cancelarModal\').style.display=\'none\'">&times;</span>
                        <h2>Confirmar cancelación</h2>
                        <p>¿Estás seguro de que deseas cancelar esta cita?</p>
                        <form method="post" action="">
                            <input type="hidden" name="citaid" value="' . $citaid . '">
                            <button type="submit" name="confirm_cancel" class="btn-cancel">Sí, cancelar cita</button>
                            <button type="button" class="btn-edit" onclick="document.getElementById(\'cancelarModal\').style.display=\'none\'">No, volver</button>
                        </form>
                    </div>
                </div>
                ';
            } else {
                echo '<script>alert("Cita no encontrada o no tienes permiso para cancelarla."); window.location.href="citas.php";</script>';
            }
        }
    }

    // Procesar la cancelación confirmada

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_cancel'])) {
        $citaid = intval($_POST['citaid']);

        // Verificar nuevamente que la cita pertenece al usuario
        $citaQuery = $database->prepare("SELECT * FROM citas WHERE citaid = ? AND pacid = ?");
        $citaQuery->bind_param("ii", $citaid, $userid);
        $citaQuery->execute();
        $citaResult = $citaQuery->get_result();

        if ($citaResult->num_rows > 0) {
            // Eliminar la cita de la base de datos
            $deleteQuery = $database->prepare("DELETE FROM citas WHERE citaid = ?");
            $deleteQuery->bind_param("i", $citaid);
            if ($deleteQuery->execute()) {
                echo '<script>alert("Cita cancelada exitosamente."); window.location.href="citas.php";</script>';
            } else {
                echo '<script>alert("Error al cancelar la cita. Por favor, intenta de nuevo."); window.location.href="citas.php";</script>';
            }
        } else {
            echo '<script>alert("Cita no encontrada o no tienes permiso para cancelarla."); window.location.href="citas.php";</script>';
        }
    }
    
    

    // Consulta principal para obtener las citas
    /* $sqlmain = "SELECT citas.citaid, doctor.docid, doctor.docnombre, citas.fecha, citas.hora_inicio, citas.hora_fin, citas.estado, especialidades.espnombre
                FROM citas
                INNER JOIN doctor ON citas.docid = doctor.docid
                INNER JOIN especialidades ON doctor.especialidades = especialidades.id
                WHERE citas.pacid = $userid"; */

    $sqlmain = "SELECT citas.citaid, doctor.docid, doctor.docnombre, citas.fecha, citas.hora_inicio, citas.hora_fin, citas.estado, especialidades.espnombre
    FROM citas
    INNER JOIN doctor ON citas.docid = doctor.docid
    INNER JOIN especialidades ON doctor.especialidades = especialidades.id
    WHERE citas.pacid = $userid AND citas.estado != 'Cancelada'";


    if ($_POST) {
        if (!empty($_POST["sheduledate"])) {
            $sheduledate = $_POST["sheduledate"];
            $sqlmain .= " AND citas.fecha = '$sheduledate'";
        }
        if (!empty($_POST["doctor"])) {
            $doctor = $_POST["doctor"];
            $sqlmain .= " AND doctor.docid = $doctor";
        }
    }

    $sqlmain .= " ORDER BY citas.fecha ASC";
    $result = $database->query($sqlmain);
    ?>
    <div class="container">
        <div class="menu">
            <p class="profile-title"><?php echo $username; ?></p>
            <a href="../logout.php"><button class="logout-btn">Cerrar sesión</button></a>
            <div class="menu-links">
                <a href="horarios.php" class="menu-link">Horarios disponibles</a>
                <a href="citas.php" class="menu-link menu-link-active">Citas agendadas</a>
                <a href="configuracion.php" class="menu-link">Configuración</a>
            </div>
        </div>
        <div class="dash-body">
            <h2>Mis citas agendadas</h2>
            <div class="filter-container">
                <form action="" method="post">
                    <label for="sheduledate">Fecha:</label>
                    <input type="date" name="sheduledate" id="sheduledate">
                    <label for="doctor">Doctor:</label>
                    <select name="doctor" id="doctor">
                        <option value="">Escoge un doctor de la lista</option>
                        <?php
                        // Obtener lista de doctores para el filtro
                        $doctoresResult = $database->query("SELECT docid, docnombre FROM doctor");
                        while ($doctor = $doctoresResult->fetch_assoc()) {
                            echo '<option value="' . $doctor['docid'] . '">' . $doctor['docnombre'] . '</option>';
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn-primary-soft btn button-icon btn-filter">Filtrar</button>
                </form>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre del doctor</th>
                            <th>Especialidad</th>
                            <th>Fecha y hora</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows == 0) {
                            echo '<tr>
                                    <td colspan="4">
                                        <center>No se encontraron citas agendadas.</center>
                                    </td>
                                  </tr>';
                        } else {
                            $currentDateTime = new DateTime();
                            while ($row = $result->fetch_assoc()) {
                                $citaid = $row["citaid"];
                                $docid = $row["docid"];
                                $docnombre = $row["docnombre"];
                                $espnombre = $row["espnombre"];
                                $fecha = $row["fecha"];
                                $hora_inicio = substr($row["hora_inicio"], 0, 5);
                                $hora_fin = substr($row["hora_fin"], 0, 5);
                                $hora_completa = $hora_inicio . ' - ' . $hora_fin;
                                $estado = $row["estado"];

                                // Crear un objeto DateTime con la fecha y hora de la cita
                            $fechaCita = new DateTime($fecha . ' ' . $hora_inicio);
                            // Calcular la diferencia en horas entre la fecha actual y la fecha de la cita
                            $interval = $currentDateTime->diff($fechaCita);
                            $hoursDifference = ($interval->days * 24) + $interval->h;

                            // Generar la fila de la tabla
                            echo '<tr>
                                    <td>' . $docnombre . '</td>
                                    <td>' . $espnombre . '</td>
                                    <td>' . $fecha . ' ' . $hora_completa . '</td>
                                    <td>' . $estado . '</td> 
                                    <td>';

                            // Mostrar los botones de cancelar y editar solo si faltan más de 48 horas para la cita
                            if ($fechaCita > $currentDateTime && $hoursDifference > 48) {
                                echo '<a href="?action=drop&id=' . $citaid . '"><button class="btn-cancel">Cancelar</button></a>
                                    <button class="btn-edit" onclick="openEditModal(' . $citaid . ', ' . $docid . ', \'' . $fecha . '\', \'' . $docnombre . '\', \'' . $hora_completa . '\')">Editar</button>';
                            }

                            echo '</td></tr>';


                               /*  echo '<tr>
                                        <td>' . $docnombre . '</td>
                                        <td>' . $espnombre . '</td>
                                        <td>' . $fecha . ' ' . $hora_completa . '</td>
                                        <td>
                                            <a href="?action=drop&id=' . $citaid . '"><button class="btn-cancel">Cancelar</button></a>
                                            <button class="btn-edit" onclick="openEditModal(' . $citaid . ', ' . $docid . ', \'' . $fecha . '\', \'' . $docnombre . '\', \'' . $hora_completa . '\')">Editar</button>
                                        </td>
                                      </tr>'; */
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for editing appointment -->
    <div id="editarModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Editar Cita</h2>
            <form id="editarForm">
                <input type="hidden" id="citaid" name="citaid">
                <input type="hidden" id="docid" name="docid">
                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" required>
                </div>
                <div class="form-group">
                    <label for="hora">Horas disponibles:</label>
                    <select id="hora" name="hora" required>
                        <option value="" disabled selected>Escoge una hora de la lista</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary" id="guardarCambiosBtn">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script>
        // Get modal element
        var modal = document.getElementById("editarModal");
        var span = document.getElementsByClassName("close")[0];
        var editarForm = document.getElementById("editarForm");

        // Open modal when clicking the "Editar" button
        function openEditModal(citaid, docid, fecha, docnombre, hora_completa) {
            document.getElementById("citaid").value = citaid;
            document.getElementById("docid").value = docid;
            document.getElementById("fecha").value = fecha;

            // Set min and max dates for the date input
            var fechaInput = document.getElementById("fecha");
            var now = new Date();
            now.setDate(now.getDate() + 1); // Incrementar al día siguiente completo
            now.setHours(0, 0, 0, 0); // Establecer la hora a 00:00 para permitir todo el día siguiente
            var minDate = now;
            var maxDate = new Date(now.getTime() + 29 * 24 * 60 * 60 * 1000); // 30 días desde el mínimo permitido

            // Format date to yyyy-mm-dd for min and max attributes
            var minDateStr = minDate.toISOString().split('T')[0];
            var maxDateStr = maxDate.toISOString().split('T')[0];

            // Set min and max attributes
            fechaInput.setAttribute("min", minDateStr);
            fechaInput.setAttribute("max", maxDateStr);

            // Fetch available times for the selected doctor and date
            fetchAvailableTimes(fecha, docid, hora_completa);
            modal.style.display = "block";
            document.body.classList.add("modal-open");
        }

        // Fetch available times for the selected doctor and date
        function fetchAvailableTimes(fecha, docid, hora_completa) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "fetch_horarios2.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = xhr.responseText;
                    var options = response.trim().split("\n");
                    var uniqueOptions = new Set(options);
                    uniqueOptions.delete('');

                    // Si existe 'hora_completa', agregarla a las opciones
                    if (hora_completa) {
                        uniqueOptions.delete(hora_completa); // Eliminar duplicado si existe
                        uniqueOptions.add('<option value="' + hora_completa + '" selected>' + hora_completa + '</option>');
                    }

                    var sortedOptions = Array.from(uniqueOptions).sort((a, b) => {
                        var timeA = a.match(/\d{2}:\d{2}/)[0];
                        var timeB = b.match(/\d{2}:\d{2}/)[0];
                        return timeA.localeCompare(timeB);
                    });

                    // Obtener la fecha y hora actual
                    var now = new Date();
                    var time24HoursFromNow = new Date(now.getTime() + 24 * 60 * 60 * 1000);

                    // Filtrar horarios que sean al menos 24 horas después del momento actual
                    sortedOptions = sortedOptions.filter(option => {
                        var timeMatch = option.match(/\d{2}:\d{2}/);
                        if (timeMatch) {
                            var time = timeMatch[0];
                            var dateParts = fecha.split('-');
                            var timeParts = time.split(':');
                            var optionDateTime = new Date(dateParts[0], dateParts[1]-1, dateParts[2], timeParts[0], timeParts[1]);
                            return optionDateTime.getTime() >= time24HoursFromNow.getTime();
                        }
                        return false;
                    });

                    // Seleccionar el botón de "Guardar Cambios"
                    var submitButton = document.getElementById("guardarCambiosBtn");

                    if (sortedOptions.length === 0) {
                        document.getElementById("hora").innerHTML = '<option value="" disabled selected>No hay horarios disponibles para la fecha seleccionada</option>';
                        // Deshabilitar el botón
                        submitButton.disabled = true;
                    } else {
                        document.getElementById("hora").innerHTML = sortedOptions.join("");
                        // Habilitar el botón
                        submitButton.disabled = false;
                    }
                }
            };
            xhr.send("fecha=" + fecha + "&docid=" + docid);
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
            document.body.classList.remove("modal-open");
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
                document.body.classList.remove("modal-open");
            }
        }

        // Handle form submission for editing
        editarForm.onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(editarForm);
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "editar_cita.php", true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert(xhr.responseText);
                    modal.style.display = "none";
                    window.location.reload(); // Reload page to see updated information
                }
            };
            xhr.send(formData);
        }

        // Update available hours when a new date is selected
        document.getElementById("fecha").addEventListener("change", function() {
            var fecha = this.value;
            var docid = document.getElementById("docid").value;
            fetchAvailableTimes(fecha, docid, "");
        });
    </script>
</body>
</html>
