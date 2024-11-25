<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    
    <title>Mis citas asignadas</title>
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
        .btn-action {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .btn-action:hover {
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
    </style>
</head>
<body>
    <?php
    session_start();

    if (isset($_SESSION["usuario"])) {
        if ($_SESSION["usuario"] == "" || $_SESSION['usuario_rol'] != 'doc') {
            header("location: ../login.php");
        } else {
            $usuario = $_SESSION["usuario"];
        }
    } else {
        header("location: ../login.php");
    }

    // Importar la base de datos
    include("../conexion_db.php");
    $userrow = $database->query("SELECT * FROM doctor WHERE docusuario='$usuario'");
    $userfetch = $userrow->fetch_assoc();
    $docid = $userfetch["docid"];
    $docnombre = $userfetch["docnombre"];

    // Consulta principal para obtener las citas del doctor
    $sqlmain = "SELECT citas.citaid, paciente.pacnombre, citas.fecha, citas.hora_inicio, citas.hora_fin, citas.estado
                FROM citas
                INNER JOIN paciente ON citas.pacid = paciente.pacid
                WHERE citas.docid = $docid";

    if ($_POST) {
        if (!empty($_POST["sheduledate"])) {
            $sheduledate = $_POST["sheduledate"];
            $sqlmain .= " AND citas.fecha = '$sheduledate'";
        }
        if (!empty($_POST["paciente"])) {
            $paciente = $_POST["paciente"];
            $sqlmain .= " AND paciente.pacid = $paciente";
        }
    }

    $sqlmain .= " ORDER BY citas.fecha ASC";
    $result = $database->query($sqlmain);
    ?>
    <div class="container">
        <div class="menu">
            <p class="profile-title"><?php echo $docnombre; ?></p>
            <a href="../logout.php"><button class="logout-btn">Cerrar sesión</button></a>
            <div class="menu-links">
                <a href="citas_asignadas.php" class="menu-link menu-link-active">Citas agendadas</a>
            </div>
        </div>
        <div class="dash-body">
            <h2>Mis citas asignadas</h2>
            <div class="filter-container">
                <form action="" method="post">
                    <label for="sheduledate">Fecha:</label>
                    <input type="date" name="sheduledate" id="sheduledate">
                    <label for="paciente">Paciente:</label>
                    <select name="paciente" id="paciente">
                        <option value="">Escoge un paciente de la lista</option>
                        <?php
                        // Obtener lista de pacientes para el filtro
                        $pacientesResult = $database->query("SELECT pacid, pacnombre FROM paciente");
                        while ($paciente = $pacientesResult->fetch_assoc()) {
                            echo '<option value="' . $paciente['pacid'] . '">' . $paciente['pacnombre'] . '</option>';
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn-action">Buscar</button>
                </form>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Paciente</th>
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
                                        <center>No se encontraron citas asignadas.</center>
                                    </td>
                                  </tr>';
                        } else {
                            while ($row = $result->fetch_assoc()) {
                                $citaid = $row["citaid"];
                                $pacnombre = $row["pacnombre"];
                                $fecha = $row["fecha"];
                                $hora_inicio = substr($row["hora_inicio"], 0, 5);
                                $hora_fin = substr($row["hora_fin"], 0, 5);
                                $hora_completa = $hora_inicio . ' - ' . $hora_fin;
                                $estado = $row["estado"];

                                echo '<tr>
                                        <td>' . $pacnombre . '</td>
                                        <td>' . $fecha . ' ' . $hora_completa . '</td>
                                        <td>' . $estado . '</td>
                                        <td>';
                                
                                if ($estado == 'Pendiente') {
                                    echo '<button class="btn-action" onclick="marcarComoFinalizada(' . $citaid . ')">Marcar como finalizada</button>
                                          <button class="btn-action" onclick="reenviarRecordatorio(' . $citaid . ')">Reenviar recordatorio</button>';
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

    <script>
        function marcarComoFinalizada(citaid) {
            if (confirm("¿Está seguro de que desea marcar esta cita como finalizada?")) {
                window.location.href = "marcar_finalizada.php?citaid=" + citaid;
            }
        }

        function reenviarRecordatorio(citaid) {
            if (confirm("¿Desea reenviar un recordatorio al paciente?")) {
                window.location.href = "reenviar_recordatorio.php?citaid=" + citaid;
            }
        }
    </script>
</body>
</html>