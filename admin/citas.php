
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    
    <title>Citas Agendadas - Administrador</title>
    <style>
        /* Estilos reutilizados del paciente para mantener la consistencia */
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
        .btn-cancel {
            background-color: #d9534f;
        }
        .btn-cancel:hover {
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
        if (empty($_SESSION["usuario"]) || $_SESSION['usuario_rol'] != 'adm') {
            header("location: ../login.php");
            exit();
        }
    } else {
        header("location: ../login.php");
        exit();
    }

    $usuario = $_SESSION["usuario"];

    // Importar la base de datos
    include("../conexion_db.php");
    $userrow = $database->query("SELECT * FROM administrador WHERE admusuario='$usuario'");
    if (!$userrow || $userrow->num_rows == 0) {
        header("location: ../login.php");
        exit();
    }
    $userfetch = $userrow->fetch_assoc();
    $username = htmlspecialchars($userfetch["admusuario"], ENT_QUOTES, 'UTF-8');

    require __DIR__ . '/../vendor/autoload.php'; // Ajusta la ruta si es necesario

    use Twilio\Rest\Client;

    // **LÓGICA PARA REENVIAR EL RECORDATORIO**
    if (isset($_GET['action']) && $_GET['action'] == 'reenviar') {
        if (isset($_GET['id'])) {
            $citaid = intval($_GET['id']);

            // Verificar que la cita existe
            $citaQuery = $database->prepare("SELECT citas.*, paciente.pactelf, paciente.pacnombre 
                                            FROM citas 
                                            INNER JOIN paciente ON citas.pacid = paciente.pacid 
                                            WHERE citas.citaid = ?");
            $citaQuery->bind_param("i", $citaid);
            $citaQuery->execute();
            $citaResult = $citaQuery->get_result();

            if ($citaResult->num_rows > 0) {
                $cita = $citaResult->fetch_assoc();

                // Tus credenciales de Twilio (reemplázalas con las correctas)
                $sid = '';
                $token = '';
                $client = new Client($sid, $token);

                // Información del paciente y cita
                $telefono_paciente = $cita['pactelf'];
                $nombre_paciente = $cita['pacnombre'];
                $fecha_cita = $cita['fecha'];
                $hora_inicio = $cita['hora_inicio'];

                // Mensaje de recordatorio
                $mensaje = "Hola $nombre_paciente, le recordamos que tiene una cita médica programada para el $fecha_cita a las $hora_inicio. Por favor, asegúrese de asistir puntualmente.";

                try {
                    // Enviar el mensaje de WhatsApp
                    $message = $client->messages->create(
                        "whatsapp:$telefono_paciente", // Número del destinatario con 'whatsapp:' como prefijo
                        [
                            'from' => 'whatsapp:+14155238886', // Número de la Sandbox de Twilio
                            'body' => $mensaje
                        ]
                    );

                    // Actualizar la base de datos para indicar que se reenviaron los recordatorios
                    $updateQuery = $database->prepare("UPDATE citas SET recordatorio_reenviado = 1 WHERE citaid = ?");
                    $updateQuery->bind_param("i", $citaid);
                    if ($updateQuery->execute()) {
                        echo '<script>alert("Recordatorio reenviado exitosamente."); window.location.href="citas.php";</script>';
                    } else {
                        echo '<script>alert("Error al actualizar el estado del recordatorio."); window.location.href="citas.php";</script>';
                    }
                } catch (Exception $e) {
                    // Mostrar un mensaje de error en caso de fallo al enviar el mensaje de WhatsApp
                    echo '<script>alert("Error al enviar el mensaje de WhatsApp: ' . $e->getMessage() . '"); window.location.href="citas.php";</script>';
                }
            } else {
                echo '<script>alert("Cita no encontrada."); window.location.href="citas.php";</script>';
            }
        }
    }

    // Manejar la cancelación de citas
    if (isset($_GET['action']) && $_GET['action'] == 'drop') {
        if (isset($_GET['id'])) {
            $citaid = intval($_GET['id']);

            // Verificar que la cita existe
            $citaQuery = $database->prepare("SELECT * FROM citas WHERE citaid = ?");
            $citaQuery->bind_param("i", $citaid);
            $citaQuery->execute();
            $citaResult = $citaQuery->get_result();

            if ($citaResult->num_rows > 0) {
                // Mostrar un modal de confirmación
                echo '
                <div id="cancelarModal" class="modal" style="display:block;">
                    <div class="modal-content">
                        <span class="close" onclick="closeCancelarModal()">&times;</span>
                        <h2>Confirmar cancelación</h2>
                        <p>¿Estás seguro de que deseas cancelar esta cita?</p>
                        <form method="post" action="citas.php">
                            <input type="hidden" name="citaid" value="' . $citaid . '">
                            <button type="submit" name="confirm_cancel" class="btn-cancel">Sí, cancelar cita</button>
                            <button type="button" class="btn-edit" onclick="closeCancelarModal()">No, volver</button>
                        </form>
                    </div>
                </div>
                <script>
                    // Remover los parámetros GET de la URL
                    if (window.location.search.includes(\'action=drop\')) {
                        var newURL = window.location.origin + window.location.pathname;
                        window.history.replaceState({}, document.title, newURL);
                    }
                    function closeCancelarModal() {
                        document.getElementById(\'cancelarModal\').style.display = \'none\';
                        var newURL = window.location.origin + window.location.pathname;
                        window.history.replaceState({}, document.title, newURL);
                    }
                </script>
                
                ';
            } else {
                echo '<script>alert("Cita no encontrada."); window.location.href="cita.php";</script>';
            }
        }
    }

    // Procesar la cancelación confirmada
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_cancel'])) {
        $citaid = intval($_POST['citaid']);

        // Verificar nuevamente que la cita existe
        $citaQuery = $database->prepare("SELECT * FROM citas WHERE citaid = ?");
        $citaQuery->bind_param("i", $citaid);
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
            echo '<script>alert("Cita no encontrada."); window.location.href="citas.php";</script>';
        }
    }

    // Consulta principal para obtener todas las citas
    $sqlmain = "SELECT citas.citaid, doctor.docid, doctor.docnombre, citas.fecha, citas.hora_inicio, citas.hora_fin, citas.estado, citas.recordatorio_reenviado, especialidades.espnombre, paciente.pacnombre
                FROM citas
                INNER JOIN doctor ON citas.docid = doctor.docid
                INNER JOIN especialidades ON doctor.especialidades = especialidades.id
                INNER JOIN paciente ON citas.pacid = paciente.pacid
                WHERE citas.estado != 'Cancelada'";

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
            <p class="profile-title">Administrador: <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></p>
            <a href="../logout.php"><button class="logout-btn">Cerrar sesión</button></a>
            <div class="menu-links">
                <a href="horarios.php" class="menu-link">Horarios disponibles</a>
                <a href="citas.php" class="menu-link menu-link-active">Citas agendadas</a>
                <a href="configuracion.php" class="menu-link">Configuración</a>
            </div>
        </div>
        <div class="dash-body">
            <h2>Citas Agendadas - Administrador</h2>
            <div class="filter-container">
                <form action="" method="post">
                    <label for="sheduledate">Fecha:</label>
                    <input type="date" name="sheduledate" id="sheduledate">
                    <label for="doctor">Doctor:</label>
                    <select name="doctor" id="doctor">
                        <option value="" disabled selected>Escoge un doctor de la lista</option>
                        <?php
                        // Obtener lista de doctores para el filtro
                        $doctoresResult = $database->query("SELECT docid, docnombre FROM doctor");
                        while ($doctor = $doctoresResult->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($doctor['docid'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($doctor['docnombre'], ENT_QUOTES, 'UTF-8') . '</option>';
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn-primary-soft btn button-icon btn-filter">Filtrar</button>
                    <button type="button" class="btn-primary-soft btn button-icon btn-add" onclick="openAgregarCitaModal()">+ Agregar nueva cita</button>
                </form>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre del paciente</th>
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
                                    <td colspan="6">
                                        <center>No se encontraron citas agendadas.</center>
                                    </td>
                                  </tr>';
                        } else {
                            $currentDateTime = new DateTime();
                            while ($row = $result->fetch_assoc()) {
                                $citaid = htmlspecialchars($row["citaid"], ENT_QUOTES, 'UTF-8');
                                $pacnombre = htmlspecialchars($row["pacnombre"], ENT_QUOTES, 'UTF-8');
                                $docnombre = htmlspecialchars($row["docnombre"], ENT_QUOTES, 'UTF-8');
                                $espnombre = htmlspecialchars($row["espnombre"], ENT_QUOTES, 'UTF-8');
                                $fecha = htmlspecialchars($row["fecha"], ENT_QUOTES, 'UTF-8');
                                $hora_inicio = substr($row["hora_inicio"], 0, 5);
                                $hora_fin = substr($row["hora_fin"], 0, 5);
                                $hora_completa = htmlspecialchars($hora_inicio . ' - ' . $hora_fin, ENT_QUOTES, 'UTF-8');
                                $estado = htmlspecialchars($row["estado"], ENT_QUOTES, 'UTF-8');
                                $recordatorioReenviado = $row['recordatorio_reenviado'];

                                $fechaCita = new DateTime($fecha . ' ' . $hora_inicio);
                                $interval = $currentDateTime->diff($fechaCita);
                                $hoursDifference = ($interval->days * 24) + $interval->h;
                                /* $minutesDifference = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i; */


                                echo '<tr>
                                        <td>' . $pacnombre . '</td>
                                        <td>' . $docnombre . '</td>
                                        <td>' . $espnombre . '</td>
                                        <td>' . $fecha . ' ' . $hora_completa . '</td>
                                        <td>' . $estado . '</td>
                                        <td>';

                                // Mostrar botón de cancelar o editar si la cita está pendiente y faltan más de 48 horas
                                /* if ($fechaCita > $currentDateTime && $hoursDifference > 48) {
                                    echo '<a href="?action=drop&id=' . $citaid . '"><button class="btn-cancel">Cancelar</button></a>
                                          <button class="btn-edit" onclick="openEditModal(\'' . $citaid . '\', \'' . $row["docid"] . '\', \'' . $fecha . '\', \'' . $docnombre . '\', \'' . $hora_completa . '\')">Editar</button>';
                                } */

                                // Mostrar botón de cancelar o editar si la cita está pendiente y faltan más de 48 horas
                                if ($fechaCita > $currentDateTime && $hoursDifference > 48) {
                                    echo '<a href="?action=drop&id=' . $citaid . '"><button class="btn-cancel">Cancelar</button></a>
                                          <button class="btn-edit" onclick="openEditModal(\'' . $citaid . '\', \'' . $row["docid"] . '\', \'' . $fecha . '\', \'' . $docnombre . '\', \'' . $hora_completa . '\')">Editar</button>';
                                }

                                // Mostrar botón de reenviar recordatorio si faltan entre 1 y 24 horas y el recordatorio no ha sido reenviado
                                if ($hoursDifference >= 1 && $hoursDifference <= 24 && $recordatorioReenviado == 0) {
                                    echo '<a href="?action=reenviar&id=' . $citaid . '"><button class="btn-edit" onclick="reenviarRecordatorio(' . $citaid . ')">Reenviar Recordatorio</button></a>';
                                }
                                            
                                echo '</td></tr>';
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
            <span class="close" onclick="closeModal()">&times;</span>
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

    
    <!-- Modal for adding a new appointment -->
    <div id="agregarCitaModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAgregarCitaModal()">&times;</span>
            <h2>Agregar nueva cita</h2>
            <form id="agregarCitaForm">
                <label for="nombre_paciente">Nombre y usuario del paciente:</label>
                <select id="nombre_paciente" name="pacid" required>
                    <option value="" disabled selected>Escoge un paciente de la lista</option>
                    <?php
                    // Obtener lista de pacientes, incluyendo el nombre del usuario
                    $pacientesResult = $database->query("SELECT pacid, pacnombre, pacusuario FROM paciente");
                    while ($paciente = $pacientesResult->fetch_assoc()) {
                        /* $nombreCompleto = htmlspecialchars($paciente['pacnombre'], ENT_QUOTES, 'UTF-8');
                        $nombreUsuario = htmlspecialchars($paciente['pacusuario'], ENT_QUOTES, 'UTF-8');
                        echo '<option value="' . htmlspecialchars($paciente['pacid'], ENT_QUOTES, 'UTF-8') . '">' . $nombreCompleto . ' - ' . $nombreUsuario . '</option>';
                    } */
                        $pacid = htmlspecialchars($paciente['pacid'], ENT_QUOTES, 'UTF-8');
                        $nombreCompleto = htmlspecialchars($paciente['pacnombre'], ENT_QUOTES, 'UTF-8');
                        $nombreUsuario = htmlspecialchars($paciente['pacusuario'], ENT_QUOTES, 'UTF-8');
                        echo '<option value="' . $pacid . '">' . $nombreCompleto . ' - ' . $nombreUsuario . '</option>';
                    }
                    ?>
                </select><br><br>

                <label for="especialidad_medica">Especialidad médica:</label>
                <select id="especialidad_medica" name="especialidad_id" required>
                    <option value="" disabled selected>Escoge una especialidad de la lista</option>
                    <?php
                    // Obtener lista de especialidades
                    $especialidadesResult = $database->query("SELECT id, espnombre FROM especialidades");
                    while ($especialidad = $especialidadesResult->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($especialidad['id'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($especialidad['espnombre'], ENT_QUOTES, 'UTF-8') . '</option>';
                    }
                    ?>
                </select><br><br>

                <label for="nombre_doctor">Nombre del doctor:</label>
                <select id="nombre_doctor" name="docid" required>
                    <option value="" disabled selected>Escoge un doctor de la lista</option>
                    <?php
                    // Obtener lista de doctores
                    $doctoresResult = $database->query("SELECT docid, docnombre FROM doctor");
                    while ($doctor = $doctoresResult->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($doctor['docid'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($doctor['docnombre'], ENT_QUOTES, 'UTF-8') . '</option>';
                    }
                    ?>
                </select><br><br>

                <label for="fecha_agregar">Fecha:</label>
                <input type="date" id="fecha_agregar" name="fecha" required><br><br>

                <label for="hora_disponible_agregar">Horas disponibles:</label>
                <select id="hora_disponible_agregar" name="hora_disponible" required>
                    <option value="" disabled selected>Escoge una hora de la lista</option>
                </select><br><br>

                <button type="submit" class="btn-primary" id="agregarCitaBtn">+ Agregar cita</button>
            </form>
        </div>
    </div>

    

    <script>
        // Get modal element
        var modal = document.getElementById("editarModal");
        var editarForm = document.getElementById("editarForm");
        var originalFecha = "";
        var originalHora = "";

        // Open modal when clicking the "Editar" button
        function openEditModal(citaid, docid, fecha, docnombre, hora_completa) {
            document.getElementById("citaid").value = citaid;
            document.getElementById("docid").value = docid;

            var fechaInput = document.getElementById("fecha");
            var now = new Date();
            var minDateStr = now.toISOString().split('T')[0];
            var maxDate = new Date(now.getTime() + 31 * 24 * 60 * 60 * 1000);
            var maxDateStr = maxDate.toISOString().split('T')[0];
            fechaInput.setAttribute("min", minDateStr);
            fechaInput.setAttribute("max", maxDateStr);
            fechaInput.value = fecha;

            originalFecha = fecha;
            originalHora = hora_completa;

            modal.style.display = "block";
            document.body.classList.add("modal-open");

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

                        var now = new Date();
                        var selectedDate = new Date(fecha);
                        if (
                            selectedDate.getFullYear() === now.getFullYear() &&
                            selectedDate.getMonth() === now.getMonth() &&
                            selectedDate.getDate() === now.getDate()
                        ) {
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

                        if (hora_completa && !timeOptions.includes(hora_completa)) {
                            timeOptions.push(hora_completa);
                        }

                        timeOptions.sort((a, b) => {
                            var timeA = a.match(/\d{2}:\d{2}/)[0];
                            var timeB = b.match(/\d{2}:\d{2}/)[0];
                            return timeA.localeCompare(timeB);
                        });

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
                    }
                }
            };
            xhr.send("fecha=" + encodeURIComponent(fecha) + "&docid=" + encodeURIComponent(docid));
        }

        editarForm.onsubmit = function(e) {
            e.preventDefault();

            var citaid = document.getElementById("citaid").value;
            var docid = document.getElementById("docid").value;
            var fecha = document.getElementById("fecha").value;
            var hora = document.getElementById("hora").value;

            if (fecha === originalFecha && hora === originalHora) {
                alert("No se realizaron cambios en la cita.");
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "editar_cita_procesar.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        var response = xhr.responseText.trim();

                        if (response === "success") {
                            alert("Cita actualizada exitosamente.");
                            closeModal();
                            window.history.replaceState({}, document.title, window.location.pathname);
                            window.location.reload(); 
                            //window.location.reload();
                        } else {
                            alert("Error al actualizar la cita: " + response);
                        }
                    } else {
                        alert("Error en la solicitud al servidor.");
                    }
                }
            };

            var data = "citaid=" + encodeURIComponent(citaid) + 
                    "&docid=" + encodeURIComponent(docid) + 
                    "&fecha=" + encodeURIComponent(fecha) + 
                    "&hora=" + encodeURIComponent(hora);

            xhr.send(data);
        };

        function closeModal() {
            modal.style.display = "none";
            document.body.classList.remove("modal-open");
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        };

        document.getElementById("fecha").addEventListener("change", function() {
            var fecha = this.value;
            var docid = document.getElementById("docid").value;
            fetchAvailableTimes(fecha, docid, "");
        });

        // Funciones para abrir y cerrar el modal de agregar cita
        function openAgregarCitaModal() {
            document.getElementById("agregarCitaModal").style.display = "block";

            // Establecer las restricciones de fecha
            var fechaInput = document.getElementById("fecha_agregar");
            var now = new Date();
            var minDate = new Date(now.getTime() + 24 * 60 * 60 * 1000); // 24 horas desde ahora
            var maxDate = new Date(now.getTime() + 31 * 24 * 60 * 60 * 1000); // 30 días desde ahora

            // Formatear las fechas a 'yyyy-mm-dd'
            function formatDate(date) {
                var year = date.getFullYear();
                var month = ('0' + (date.getMonth() + 1)).slice(-2);
                var day = ('0' + date.getDate()).slice(-2);
                return year + '-' + month + '-' + day;
            }

            fechaInput.setAttribute("min", formatDate(minDate));
            fechaInput.setAttribute("max", formatDate(maxDate));
        }

        function closeAgregarCitaModal() {
            document.getElementById("agregarCitaModal").style.display = "none";
        }

        // Obtener las horas disponibles al seleccionar la fecha
        document.getElementById("fecha_agregar").addEventListener("change", function () {
            var fecha = this.value;
            var doctorId = document.getElementById("nombre_doctor").value;

            console.log("Fecha seleccionada:", fecha);
            console.log("Doctor ID:", doctorId);

            if (fecha && doctorId) {
                // Crear una petición AJAX para obtener los horarios
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "fetch_horarios3.php", true); // Actualizado a fetch_horarios3.php
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var response = xhr.responseText.trim();
                        console.log("Respuesta del servidor:", response);

                        var horaDisponibleSelect = document.getElementById("hora_disponible_agregar");
                        horaDisponibleSelect.innerHTML = response;

                        // Verificar si hay horarios disponibles
                        if (horaDisponibleSelect.options.length > 0 && horaDisponibleSelect.options[0].value !== "") {
                            document.getElementById("agregarCitaBtn").disabled = false; // Habilitar el botón si hay horarios disponibles
                        } else {
                            document.getElementById("agregarCitaBtn").disabled = true; // Deshabilitar el botón si no hay horarios disponibles
                        }
                    }
                };

                xhr.send("fecha=" + encodeURIComponent(fecha) + "&docid=" + encodeURIComponent(doctorId));
            } else {
                console.log("Fecha o doctor no seleccionados");
            }
        });

        // Cerrar el modal cuando se hace clic fuera de él
        window.onclick = function (event) {
            var modal = document.getElementById("agregarCitaModal");
            if (event.target == modal) {
                closeAgregarCitaModal();
            }
        }

        // Manejar el envío del formulario de agregar cita
        /* document.getElementById("agregarCitaForm").addEventListener("submit", function (e) {
            e.preventDefault();

            var formData = new FormData(this);

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "agregar_cita.php", true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert(xhr.responseText);
                    closeAgregarCitaModal();
                    location.reload();
                }
            };
            xhr.send(formData);
        }); */

        document.getElementById("especialidad_medica").addEventListener("change", function () {
            var especialidadId = this.value;

            console.log("Especialidad seleccionada: " + especialidadId); // Para verificar el valor seleccionado

            if (especialidadId) {
                // Crear una petición AJAX para obtener los doctores de la especialidad seleccionada
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "fetch_doctores.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        // Actualizar el contenido del select de doctores
                        document.getElementById("nombre_doctor").innerHTML = xhr.responseText;
                    }
                };
                xhr.send("especialidad_id=" + encodeURIComponent(especialidadId));
            } else {
                // Si no hay especialidad seleccionada, limpiar el dropdown de doctores
                document.getElementById("nombre_doctor").innerHTML = '<option value="" disabled selected>Escoge un doctor de la lista</option>';
            }
        });
        
        document.getElementById("nombre_doctor").addEventListener("change", function () {
            // Vaciar el select de horas disponibles
            document.getElementById("hora_disponible_agregar").innerHTML = '<option value="" disabled selected>Escoge una hora de la lista</option>';

            // Deshabilitar el botón de agregar cita
            document.getElementById("agregarCitaBtn").disabled = true;

            // Si ya hay una fecha seleccionada, intentar obtener los horarios disponibles
            var fecha = document.getElementById("fecha_agregar").value;
            console.log("Fecha seleccionada para envío: ", fecha);
            if (fecha) {
                // Disparar el evento 'change' del input de fecha para obtener los horarios
                var event = new Event('change');
                document.getElementById("fecha_agregar").dispatchEvent(event);
            }
        });

        document.getElementById("agregarCitaForm").addEventListener("submit", function (e) {
            e.preventDefault();

            var formData = new FormData(this);

            // Separar la hora de inicio y fin
            var horaSeleccionada = document.getElementById("hora_disponible_agregar").value;
            if (horaSeleccionada) {
                var horas = horaSeleccionada.split(' - ');
                formData.append("hora_inicio", horas[0]);
                formData.append("hora_fin", horas[1]);
            } else {
                alert("Por favor selecciona una hora válida.");
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "agregar_cita.php", true);

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert(xhr.responseText);
                    closeAgregarCitaModal();
                    location.reload();
                }
            };
            xhr.send(formData);
        });

    </script>
</body>
</html>