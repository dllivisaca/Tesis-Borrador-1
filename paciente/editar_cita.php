<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    
    <title>Editar Cita</title>
    <style>
        .modal {
            display: block;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6); /* Ajustado para permitir ver el fondo suavemente gris */
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
        .btn-primary {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        body.modal-open {
            overflow: hidden;
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

    include("../conexion_db.php");
    $userrow = $database->query("SELECT * FROM paciente WHERE pacusuario='$usuario'");
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch["pacid"];
    $username = $userfetch["pacnombre"];

    if (isset($_GET['id'])) {
        $citaid = $_GET['id'];
        $sql = "SELECT * FROM citas WHERE citaid = $citaid AND pacid = $userid";
        $result = $database->query($sql);
        $cita = $result->fetch_assoc();

        if (!$cita) {
            echo "<script>alert('Cita no encontrada'); window.location.href = 'citas.php';</script>";
            exit;
        }
    } else {
        header("location: citas.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['citaid'], $_POST['docid'], $_POST['fecha'], $_POST['hora'])) {
            $citaid = intval($_POST['citaid']);
            $docid = intval($_POST['docid']);
            $fecha = $_POST['fecha'];
            list($hora_inicio, $hora_fin) = explode(' - ', $_POST['hora']);
        
            // Escapar las variables para evitar problemas de seguridad
            $fecha = $database->real_escape_string($fecha);
            $hora_inicio = $database->real_escape_string($hora_inicio);
            $hora_fin = $database->real_escape_string($hora_fin);
        
            // Actualizar la cita en la base de datos
            $sql = "UPDATE citas 
                    SET fecha = ?, hora_inicio = ?, hora_fin = ?
                    WHERE citaid = ? AND docid = ?";
                        
            $stmt = $database->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssii", $fecha, $hora_inicio, $hora_fin, $citaid, $docid);
        
                if ($stmt->execute()) {
                    echo "success";
                } else {
                    echo "Error al actualizar la cita. Intente nuevamente.";
                }
        
                $stmt->close();
            } else {
                echo "Error en la preparación de la consulta.";
            }
        } else {
            echo "Error: Faltan datos requeridos para actualizar la cita.";
        }
    } else {
        echo "Error: Solicitud no válida.";
    }
    ?>

    <!-- Modal for editing appointment -->
    <div id="editarModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Editar Cita</h2>
            <form action="" method="post">
                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" value="<?php echo $cita['fecha']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="hora_inicio">Hora de Inicio:</label>
                    <input type="time" id="hora_inicio" name="hora_inicio" value="<?php echo $cita['hora_inicio']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="hora_fin">Hora de Fin:</label>
                    <input type="time" id="hora_fin" name="hora_fin" value="<?php echo $cita['hora_fin']; ?>" required>
                </div>
                <button type="submit" class="btn-primary">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script>
        // Get modal element
        var modal = document.getElementById("editarModal");
        var span = document.getElementsByClassName("close")[0];

        // Show the modal when the page loads
        window.onload = function() {
            modal.style.display = "block";
            document.body.classList.add("modal-open");
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
            document.body.classList.remove("modal-open");
            window.location.href = 'citas.php';
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
                document.body.classList.remove("modal-open");
                window.location.href = 'citas.php';
            }
        }
    </script>
</body>
</html>
