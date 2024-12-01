<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/doctor/citas.css">
    
    <title>Mis citas asignadas</title>
    <!-- <style>
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
    </style> -->
</head>
<body>
    <?php
    error_reporting(E_ERROR | E_PARSE);

    date_default_timezone_set('America/Guayaquil'); 

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

    // Importar Twilio y cargar el autoload
    require __DIR__ . '/../vendor/autoload.php'; // Ajusta la ruta si es necesario
    use Twilio\Rest\Client;

    function normalizePhoneNumber($phoneNumber) {
        // Eliminar espacios, guiones, paréntesis y signos '+'
        $phoneNumber = preg_replace('/[\s\-()+]/', '', $phoneNumber);
    
        // Si el número comienza con '0', eliminarlo
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = substr($phoneNumber, 1);
        }
    
        // Si el número no comienza con '593', agregarlo
        if (substr($phoneNumber, 0, 3) !== '593') {
            $phoneNumber = '593' . $phoneNumber;
        }
    
        // Agregar el signo '+' al inicio
        $phoneNumber = '+' . $phoneNumber;
    
        return $phoneNumber;
    }

    // **LÓGICA PARA REENVIAR EL RECORDATORIO**
    if (isset($_GET['action']) && $_GET['action'] == 'reenviar') {
        if (isset($_GET['citaid'])) {
            $citaid = intval($_GET['citaid']);

            // Verificar que la cita existe y pertenece al doctor
            $citaQuery = $database->prepare("SELECT citas.*, paciente.pactelf, paciente.pacnombre, paciente.pacid 
                                            FROM citas 
                                            INNER JOIN paciente ON citas.pacid = paciente.pacid 
                                            WHERE citas.citaid = ? AND citas.docid = ?");
            $citaQuery->bind_param("ii", $citaid, $docid);
            $citaQuery->execute();
            $citaResult = $citaQuery->get_result();

            if ($citaResult->num_rows > 0) {
                $cita = $citaResult->fetch_assoc();

                // Tus credenciales de Twilio (asegúrate de mantenerlas seguras y no incluirlas en el código compartido)
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
                        // **Registrar en el archivo de log**
                        $log_file = __DIR__ . "/recordatorios_log.txt";
                        $fecha_actual = date('Y-m-d H:i:s');

                        // Calcular la diferencia en horas
                        $fecha_hora_actual = strtotime($fecha_actual);
                        $fecha_hora_cita = strtotime("$fecha_cita $hora_inicio");
                        $horas_diferencia = ($fecha_hora_cita - $fecha_hora_actual) / 3600;
                        $horas_diferencia_formateada = number_format($horas_diferencia, 12);

                        $log_message = "Reenvío de recordatorio: $fecha_actual \nCita ID: $citaid - Diferencia en horas: $horas_diferencia_formateada - Fecha y hora de la cita: $fecha_cita $hora_inicio\n";
                        file_put_contents($log_file, $log_message, FILE_APPEND);
                        // **Fin del registro en el log**

                        echo '<script>alert("Recordatorio reenviado exitosamente."); window.location.href="citas.php";</script>';
                    } else {
                        echo '<script>alert("Error al actualizar el estado del recordatorio."); window.location.href="citas.php";</script>';
                    }
                } catch (Exception $e) {
                    // Mostrar un mensaje de error en caso de fallo al enviar el mensaje de WhatsApp
                    echo '<script>alert("Error al enviar el mensaje de WhatsApp: ' . $e->getMessage() . '"); window.location.href="citas.php";</script>';
                }
            } else {
                echo '<script>alert("Cita no encontrada o no pertenece a este doctor."); window.location.href="citas.php";</script>';
            }
        }
    }

    // **LÓGICA PARA MARCAR LA CITA COMO FINALIZADA**
    if (isset($_GET['action']) && $_GET['action'] == 'finalizar') {
        if (isset($_GET['citaid'])) {
            $citaid = intval($_GET['citaid']);

            // Verificar que la cita existe y pertenece al doctor
            $citaQuery = $database->prepare("SELECT citas.*, paciente.pacnombre, paciente.pactelf 
                                         FROM citas 
                                         INNER JOIN paciente ON citas.pacid = paciente.pacid 
                                         WHERE citaid = ? AND docid = ?");
            $citaQuery->bind_param("ii", $citaid, $docid);
            $citaQuery->execute();
            $citaResult = $citaQuery->get_result();

            if ($citaResult->num_rows > 0) {
                $cita = $citaResult->fetch_assoc();

                // Actualizar el estado de la cita a 'finalizada'
                $updateQuery = $database->prepare("UPDATE citas SET estado = 'finalizada' WHERE citaid = ?");
                $updateQuery->bind_param("i", $citaid);
                if ($updateQuery->execute()) {
                    // Credenciales de Twilio
                    $sid = ''; // Reemplaza con tu SID
                    $token = ''; // Reemplaza con tu Token
                    $client = new Client($sid, $token);

                    // Datos del paciente
                    $telefonoPaciente = $cita['pactelf'];
                    $nombrePaciente = $cita['pacnombre'];

                    // Normalizar el número de teléfono
                    $telefonoPacienteE164 = normalizePhoneNumber($telefonoPaciente);

                    // Enviar la encuesta
                    try {
                        $mensaje1 = "Hola $nombrePaciente, gracias por visitarnos. ¿Cómo calificaría el servicio recibido hoy?\n\n" .
                                    "1: Muy insatisfecho\n" .
                                    "2: Insatisfecho\n" .
                                    "3: Neutral\n" .
                                    "4: Satisfecho\n" .
                                    "5: Muy satisfecho";

                        $client->messages->create(
                            "whatsapp:$telefonoPacienteE164",
                            [
                                'from' => 'whatsapp:+14155238886',
                                'body' => $mensaje1
                            ]
                        );

                        // Registrar en la tabla de encuestas
                        $fechaEnvio = date('Y-m-d H:i:s');
                        $insertQuery = $database->prepare("INSERT INTO respuestas_encuestas (numero_cliente, fecha_envio, estado) VALUES (?, ?, 'esperando_calificacion')");
                        $insertQuery->bind_param("ss", $telefonoPacienteE164, $fechaEnvio);
                        $insertQuery->execute();

                        echo '<script>alert("Cita marcada como finalizada y encuesta enviada."); window.location.href="citas.php";</script>';
                    } catch (Exception $e) {
                        echo '<script>alert("Cita marcada como finalizada, pero ocurrió un error al enviar la encuesta: ' . $e->getMessage() . '"); window.location.href="citas.php";</script>';
                    }
                } else {
                    echo '<script>alert("Error al actualizar el estado de la cita."); window.location.href="citas.php";</script>';
                }
            } else {
                echo '<script>alert("Cita no encontrada o no pertenece a este doctor."); window.location.href="citas.php";</script>';
            }
        }
    }

    // Consulta principal para obtener las citas del doctor
    $sqlmain = "SELECT 
    citas.citaid, 
    paciente.pacnombre, 
    citas.fecha, 
    citas.hora_inicio, 
    citas.hora_fin, 
    citas.estado, 
    citas.recordatorio_reenviado
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
            <div class="profile-container">
                <img src="../img/logo.png" alt="Logo" class="menu-logo">
                
                <p class="profile-title"><?php echo substr($docnombre,0,13)  ?>..</p>
            </div>
            <a href="../logout.php"><button class="btn-logout">Cerrar sesión</button></a>
            <div class="linea-separadora"></div>
            <div class="menu-links">
                
                <a href="citas.php" class="menu-link menu-link-active">Citas agendadas</a>
                
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
                Mis citas asignadas
                </p>
            </div>
        </div>

        <div class="filter-row">
            <form method="POST">
            <label for="sheduledate">Fecha:</label>
                    <input type="date" name="sheduledate" id="sheduledate" value="<?php echo isset($_POST['sheduledate']) ? $_POST['sheduledate'] : ''; ?>">
                    <label for="paciente">Paciente:</label>
                    <select name="paciente" id="paciente">
                        <option value="">Escoge un paciente de la lista</option>
                        
                        <?php
                        // Obtener lista de pacientes para el filtro
                        $pacientesResult = $database->query("SELECT pacid, pacnombre FROM paciente");
                        while ($paciente = $pacientesResult->fetch_assoc()) {
                            $selected = (isset($_POST['paciente']) && $_POST['paciente'] == $paciente['pacid']) ? 'selected' : '';
                            echo '<option value="' . $paciente['pacid'] . '" ' . $selected . '>' . $paciente['pacnombre'] . '</option>';
                        }
                        ?>
                    </select>
                    
                <button type="submit" class="btn-primary-soft btn button-icon btn-filter">Buscar</button>
                <a href="http://localhost/login/doctor/citas.php" class="btn-primary-soft btn button-icon btn-filter">Limpiar filtros</a>
            </form>
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
                            $currentDateTime = new DateTime();

                            while ($row = $result->fetch_assoc()) {
                                $citaid = $row["citaid"];
                                $pacnombre = $row["pacnombre"];
                                $fecha = $row["fecha"];
                                $hora_inicio = substr($row["hora_inicio"], 0, 5);
                                $hora_fin = substr($row["hora_fin"], 0, 5);
                                $hora_completa = $hora_inicio . ' - ' . $hora_fin;
                                $estado = $row["estado"];
                                $recordatorioReenviado = $row['recordatorio_reenviado'];

                                // Calcular la diferencia en horas
                                $fechaCita = new DateTime($fecha . ' ' . $hora_inicio);
                                $interval = $currentDateTime->diff($fechaCita);
                                $hoursDifference = ($interval->days * 24) + $interval->h + ($interval->i / 60);

                                echo '<tr>
                                        <td>' . $pacnombre . '</td>
                                        <td>' . $fecha . ' ' . $hora_completa . '</td>
                                        <td>' . $estado . '</td>
                                        <td>';
                                
                                if ($estado == 'pendiente') {
                                    echo '<button class="btn-action" onclick="marcarComoFinalizada(' . $citaid . ')">Marcar como finalizada</button>';
                                    
                                    // Mostrar botón de reenviar recordatorio si faltan entre 1 y 24 horas y el recordatorio no ha sido reenviado
                                    if ($hoursDifference >= 1 && $hoursDifference <= 24 && $recordatorioReenviado == 0) {
                                        echo '<button class="btn-action" onclick="reenviarRecordatorio(' . $citaid . ')">Reenviar recordatorio</button>';
                                    }
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
                window.location.href = "citas.php?action=finalizar&citaid=" + citaid;
            }
        }

        function reenviarRecordatorio(citaid) {
            if (confirm("¿Desea reenviar un recordatorio al paciente?")) {
                window.location.href = "citas.php?action=reenviar&citaid=" + citaid;
            }
        }
    </script>
</body>
</html>