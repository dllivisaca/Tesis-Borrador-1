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

    // Consulta principal para obtener todas las citas
    $sqlmain = "SELECT citas.citaid, doctor.docid, doctor.docnombre, citas.fecha, citas.hora_inicio, citas.hora_fin, citas.estado, especialidades.espnombre, paciente.pacnombre
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
                <a href="citas_admin.php" class="menu-link menu-link-active">Citas agendadas</a>
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
                        <option value="">Escoge un doctor de la lista</option>
                        <?php
                        // Obtener lista de doctores para el filtro
                        $doctoresResult = $database->query("SELECT docid, docnombre FROM doctor");
                        while ($doctor = $doctoresResult->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($doctor['docid'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($doctor['docnombre'], ENT_QUOTES, 'UTF-8') . '</option>';
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

                                $fechaCita = new DateTime($fecha . ' ' . $hora_inicio);
                                $interval = $currentDateTime->diff($fechaCita);
                                $hoursDifference = ($interval->days * 24) + $interval->h;

                                echo '<tr>
                                        <td>' . $pacnombre . '</td>
                                        <td>' . $docnombre . '</td>
                                        <td>' . $espnombre . '</td>
                                        <td>' . $fecha . ' ' . $hora_completa . '</td>
                                        <td>' . $estado . '</td>
                                        <td>';
                                
                                if ($fechaCita > $currentDateTime && $hoursDifference > 48) {
                                    echo '<a href="?action=drop&id=' . $citaid . '"><button class="btn-cancel">Cancelar</button></a>
                                          <button class="btn-edit" onclick="openEditModal(' . $citaid . ', ' . $row["docid"] . ', \'' . $fecha . '\', \'' . $docnombre . '\', \'' . $hora_completa . '\')">Editar</button>';
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

    <script>
        // Get modal element
        var modal = document.getElementById("editarModal");
        var editarForm = document.getElementById("editarForm");

        // Open modal when clicking the "Editar" button
        function openEditModal(citaid, docid, fecha, docnombre, hora_completa) {
            document.getElementById("citaid").value = citaid;
            document.getElementById("docid").value = docid;

            var fechaInput = document.getElementById("fecha");
            var now = new Date();
            var minDateStr = now.toISOString().split('T')[0];
            var maxDate = new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000);
            var maxDateStr = maxDate.toISOString().split('T')[0];
            fechaInput.setAttribute("min", minDateStr);
            fechaInput.setAttribute("max", maxDateStr);
            fechaInput.value = fecha;

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
                            window.location.reload();
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

    </script>
</body>
</html>
