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

    // Consulta principal para obtener las citas
    $sqlmain = "SELECT citas.citaid, doctor.docid, doctor.docnombre, citas.fecha, citas.hora_inicio, citas.hora_fin, citas.estado, especialidades.espnombre
                FROM citas
                INNER JOIN doctor ON citas.docid = doctor.docid
                INNER JOIN especialidades ON doctor.especialidades = especialidades.id
                WHERE citas.pacid = $userid";

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
                            while ($row = $result->fetch_assoc()) {
                                $citaid = $row["citaid"];
                                $docid = $row["docid"];
                                $docnombre = $row["docnombre"];
                                $espnombre = $row["espnombre"];
                                $fecha = $row["fecha"];
                                $hora_inicio = substr($row["hora_inicio"], 0, 5);
                                $hora_fin = substr($row["hora_fin"], 0, 5);
                                $hora_completa = $hora_inicio . ' - ' . $hora_fin;

                                echo '<tr>
                                        <td>' . $docnombre . '</td>
                                        <td>' . $espnombre . '</td>
                                        <td>' . $fecha . ' ' . $hora_completa . '</td>
                                        <td>
                                            <a href="?action=drop&id=' . $citaid . '"><button class="btn-cancel">Cancelar</button></a>
                                            <button class="btn-edit" onclick="openEditModal(' . $citaid . ', ' . $docid . ', \'' . $fecha . '\', \'' . $docnombre . '\', \'' . $hora_completa . '\')">Editar</button>
                                        </td>
                                      </tr>';
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
                <button type="submit" class="btn-primary">Guardar Cambios</button>
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
                    var uniqueOptions = new Set(response.trim().split("\n"));
                    uniqueOptions.delete('');
                    if (hora_completa) {
                        uniqueOptions.delete(hora_completa); // Remove duplicate if exists
                        uniqueOptions.add('<option value="' + hora_completa + '" selected>' + hora_completa + '</option>');
                    }
                    document.getElementById("hora").innerHTML = Array.from(uniqueOptions).join("");
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
