<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/paciente/citas.css">
    
    <title>Mis citas agendadas</title>
    
</head>
<body>
    <?php
    error_reporting(E_ERROR | E_PARSE);

    date_default_timezone_set('America/Guayaquil');

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
            <div class="profile-container">
                <img src="../img/logo.png" alt="Logo" class="menu-logo">
                
                <p class="profile-title"><?php echo substr($username,0,13)  ?>..</p>
                
            </div>


            <a href="../logout.php"><button class="btn-logout">Cerrar sesión</button></a>
            <div class="linea-separadora"></div>
            <div class="menu-links">
                <a href="citas.php" class="menu-link menu-link-active">Citas agendadas</a>
                <a href="horarios.php" class="menu-link">Horarios disponibles</a>
                <a href="ayuda.php" class="menu-link">Ayuda</a>
            </div>
        </div>

        <div class="dash-body">
            <div class="header-actions">
            <!-- Sección izquierda: Botón Atrás y barra de búsqueda -->
            <div class="header-inline">
                <a href="citas.php">
                    <button class="btn-action">← Atrás</button>
                </a>
                <p class="heading-main12" style="margin: 0; font-size: 17px; color: rgb(49, 49, 49); align-self: left;">
                Mis citas agendadas
                </p>
            </div>
        </div>

        <div class="filter-row">
            <form method="POST">
            <label for="sheduledate">Fecha:</label>
                    <input type="date" name="sheduledate" id="sheduledate" value="<?php echo isset($_POST['sheduledate']) ? $_POST['sheduledate'] : ''; ?>">
                    <label for="doctor">Doctor:</label>
                    <select name="doctor" id="doctor">
                        <option value="">Escoge un doctor de la lista</option>
                        <?php
                        // Obtener lista de doctores para el filtro
                        $doctoresResult = $database->query("SELECT docid, docnombre FROM doctor");
                        while ($doctor = $doctoresResult->fetch_assoc()) {
                            $selected = (isset($_POST['doctor']) && $_POST['doctor'] == $doctor['docid']) ? 'selected' : '';
                            echo '<option value="' . $doctor['docid'] . '" ' . $selected . '>' . $doctor['docnombre'] . '</option>';
                        }
                        ?>
                    </select>
                <button type="submit" class="btn-primary-soft btn button-icon btn-filter">Buscar</button>
                <a href="http://localhost/login/paciente/citas.php" class="btn-primary-soft btn button-icon btn-filter">Limpiar filtros</a>
            </form>
        </div>


        
        <div class="table-container">

            <div class="abc scroll">
            <div class="header-row">
                <button onclick="window.location.href='horarios.php';" class="btn-add-cita">+ Agregar nueva cita</button>
            </div>
                <table class="sub-table scrolldown" border="0">
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
                                    /* echo '<a href="?action=drop&id=' . $citaid . '"><button class="btn-cancel">Cancelar</button></a>
                                        <button class="btn-edit" onclick="openEditModal(' . $citaid . ', ' . $docid . ', \'' . $fecha . '\', \'' . $docnombre . '\', \'' . $hora_completa . '\')">Editar</button>'; */
                                    echo '<div class="button-container">
                                        <button class="btn-cancel" onclick="openCancelModal(' . $citaid . ')">Cancelar</button>
                                    
                                        <button class="btn-edit" onclick="openEditModal(\'' . $citaid . '\', \'' . $docid . '\', \'' . $fecha . '\', \'' . $docnombre . '\', \'' . $hora_completa . '\')">Editar</button>
                                    </div>';

                                }

                                echo '</td></tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
            

    <!-- Modal for editing appointment -->
    <div id="editarModal" class="modal">
        <div class="modal-content">
            <span class="close" data-close-modal="editarModal">&times;</span>
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

    <div id="cancelarModal" class="modal">
        <div class="modal-content">
            <span class="close" data-close-modal="cancelarModal">&times;</span>
            <h2>Confirmar Cancelación</h2>
            <p>¿Está seguro de que desea cancelar esta cita?</p>
            <form id="cancelarForm" method="post">
                <input type="hidden" name="citaid" id="cancelarCitaId">
                <button type="submit" name="confirm_cancel" class="btn-cancel">Sí, Cancelar</button>
                <button type="button" class="btn-edit" data-close-modal="cancelarModal">No, Volver</button>
            </form>
        </div>
    </div>

    <script>
        function openCancelModal(citaid) {
        // Asigna el citaid al campo oculto
        document.getElementById('cancelarCitaId').value = citaid;

        // Muestra el modal
        var modal = document.getElementById('cancelarModal');
        modal.style.display = 'block';
        document.body.classList.add('modal-open');
    }

        // Attach click event to all elements with data-close-modal attribute
        var closeElements = document.querySelectorAll('[data-close-modal]');
        for (var i = 0; i < closeElements.length; i++) {
            closeElements[i].onclick = function() {
                var modalId = this.getAttribute('data-close-modal');
                var modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'none';
                    document.body.classList.remove('modal-open');
                }
            };
        }

        // Close modals when clicking outside of them
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
        };


        
        // Get modal element
        var modal = document.getElementById("editarModal");
        var span = document.getElementsByClassName("close")[0];
        var editarForm = document.getElementById("editarForm");
        var originalFecha = "";
        var originalHora = "";

        // Open modal when clicking the "Editar" button
        function openEditModal(citaid, docid, fecha, docnombre, hora_completa) {
            document.getElementById("citaid").value = citaid;
            document.getElementById("docid").value = docid;

            // Set min and max dates for the date input
            var fechaInput = document.getElementById("fecha");
            var now = new Date();
            now.setDate(now.getDate() + 1);
            var minDateStr = now.toISOString().split('T')[0];
            var maxDate = new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000);       
            var maxDateStr = maxDate.toISOString().split('T')[0];
            fechaInput.setAttribute("min", minDateStr);
            fechaInput.setAttribute("max", maxDateStr);
            fechaInput.value = fecha;

            originalFecha = fecha;
            originalHora = hora_completa;

            modal.style.display = "block";
            document.body.classList.add("modal-open");

            // Fetch available times for the selected doctor and date
            fetchAvailableTimes(fecha, docid, hora_completa);
        }
        
        function fetchAvailableTimes(fecha, docid, hora_completa) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "fetch_horarios2.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        var response = xhr.responseText.trim();
                        var options = response.split("\n").filter(option => option.trim() !== '');
                        var timeOptions = options.map(option => option.trim());

                        // Obtener la fecha y hora actuales
                        var now = new Date();

                        // Filtrar las horas si la fecha seleccionada es hoy
                        var selectedDate = new Date(fecha);
                        if (
                            selectedDate.getFullYear() === now.getFullYear() &&
                            selectedDate.getMonth() === now.getMonth() &&
                            selectedDate.getDate() === now.getDate()
                        ) {
                            // Si la fecha es hoy, filtrar horarios que ya pasaron
                            timeOptions = timeOptions.filter(time => {
                                var timeMatch = time.match(/^\d{2}:\d{2}/);
                                if (timeMatch) {
                                    var [_, hours, minutes] = timeMatch;
                                    var timeDate = new Date(selectedDate);
                                    timeDate.setHours(parseInt(hours, 10), parseInt(minutes, 10));

                                    return timeDate > now;
                                }
                                return false;
                            });
                        }

                        // Include the current appointment time in the list if it's not already there
                        if (hora_completa && !timeOptions.includes(hora_completa)) {
                            timeOptions.push(hora_completa);
                        }

                        // Sort the times logically
                        timeOptions.sort((a, b) => {
                            var timeA = a.match(/\d{2}:\d{2}/)[0];
                            var timeB = b.match(/\d{2}:\d{2}/)[0];
                            return timeA.localeCompare(timeB);
                        });

                        // Generate the <option> elements for the dropdown
                        var optionElements = timeOptions.map(time => {
                            if (time === hora_completa) {
                                return '<option value="' + time + '" selected>' + time + '</option>';
                            } else {
                                return '<option value="' + time + '">' + time + '</option>';
                            }
                        });

                        var submitButton = document.getElementById("guardarCambiosBtn");
                        var horaSelect = document.getElementById("hora");

                        if (optionElements.length === 0) {
                            horaSelect.innerHTML = '<option value="" disabled selected>No hay horarios disponibles para la fecha seleccionada</option>';
                            submitButton.disabled = true;
                        } else {
                            horaSelect.innerHTML = optionElements.join("");
                            submitButton.disabled = false;
                        }
                    } else {
                        console.error("Failed to fetch times. Status:", xhr.status); // Debugging
                    }
                }
            };
            xhr.send("fecha=" + encodeURIComponent(fecha) + "&docid=" + encodeURIComponent(docid));
        }

        // Handle form submission for editing appointment
        editarForm.onsubmit = function(e) {
            e.preventDefault();

            // Get form data
            var citaid = document.getElementById("citaid").value;
            var docid = document.getElementById("docid").value;
            var fecha = document.getElementById("fecha").value;
            var hora = document.getElementById("hora").value;

            if (fecha === originalFecha && hora === originalHora) {
                alert("No se realizaron cambios en la cita.");
                return;
            }

            // AJAX request to update the appointment
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "editar_cita_procesar.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        var response = xhr.responseText.trim();

                        // Handle server response
                        if (response === "success") {
                            alert("Cita actualizada exitosamente.");
                            modal.style.display = "none";
                            window.location.reload(); // Reload the page to reflect changes
                        } else {
                            alert("Error al actualizar la cita: " + response);
                        }
                    } else {
                        alert("Error en la solicitud al servidor.");
                    }
                }
            };

            // Send data to the server
            var data = "citaid=" + encodeURIComponent(citaid) + 
                    "&docid=" + encodeURIComponent(docid) + 
                    "&fecha=" + encodeURIComponent(fecha) + 
                    "&hora=" + encodeURIComponent(hora);

            xhr.send(data);
    };

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
            document.body.classList.remove("modal-open");
        };

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
                document.body.classList.remove("modal-open");
            }
        };

        // Update available hours when a new date is selected
        document.getElementById("fecha").addEventListener("change", function() {
            var fecha = this.value;
            var docid = document.getElementById("docid").value;
            fetchAvailableTimes(fecha, docid, "");
        });

    </script>
</body>
</html>
