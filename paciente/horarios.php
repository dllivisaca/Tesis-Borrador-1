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
    
    date_default_timezone_set('Asia/Kolkata');
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
                $sqlmain= "SELECT doctor.docid, doctor.docnombre, especialidades.espnombre, disponibilidad_doctor.dia_semana, disponibilidad_doctor.horainicioman, disponibilidad_doctor.horafinman, disponibilidad_doctor.horainiciotar, disponibilidad_doctor.horafintar FROM doctor LEFT JOIN disponibilidad_doctor ON doctor.docid = disponibilidad_doctor.docid LEFT JOIN especialidades ON doctor.especialidades = especialidades.id ORDER BY doctor.docnombre, disponibilidad_doctor.dia_semana";
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
                                    $current_doctor = '';
                                    $current_especialidad = '';
                                    while($row = $result->fetch_assoc()){
                                        $docid = $row['docid'];
                                        $docnombre = $row['docnombre'];
                                        $espnombre = $row['espnombre'];
                                        $dia_semana = $row['dia_semana'];
                                        $horainicioman = ($row['horainicioman'] != '00:00:00') ? substr($row['horainicioman'], 0, 5) : '';
                                        $horafinman = ($row['horafinman'] != '00:00:00') ? substr($row['horafinman'], 0, 5) : '';
                                        $horainiciotar = ($row['horainiciotar'] != '00:00:00') ? substr($row['horainiciotar'], 0, 5) : '';
                                        $horafintar = ($row['horafintar'] != '00:00:00') ? substr($row['horafintar'], 0, 5) : '';

                                        if($current_doctor != $docnombre){
                                            if($current_doctor != ''){
                                                if ($horario_col_empty) {
                                                    echo '<p>No se encontraron horarios disponibles</p>';
                                                }
                                                echo '</div></td>';
                                                echo '<td>';
                                                if (!$horario_col_empty) {
                                                    echo '<button class="login-btn btn-primary-soft btn agendar-btn" style="padding-top:11px;padding-bottom:11px;width:100%" data-docid="'.$docid.'" data-docnombre="'.$current_doctor.'" data-espnombre="'.$current_especialidad.'">Agendar cita</button>';
                                                }
                                                echo '</td></tr>';
                                            }
                                            $current_doctor = $docnombre;
                                            $current_especialidad = $espnombre;
                                            $horario_col_empty = true;
                                            echo '<tr>
                                                    <td>'.$docnombre.'</td>
                                                    <td>'.$espnombre.'</td>
                                                    <td>
                                                        <div class="horario-col">';
                                        }

                                        if ($horainicioman != '' && $horafinman != '') {
                                            echo '<div class="horario-item">
                                                    <b>'.$dia_semana.'</b><br>
                                                    '.$horainicioman.' - '.$horafinman.'<br>
                                                  </div>';
                                            $horario_col_empty = false;
                                        }

                                        if ($horainiciotar != '' && $horafintar != '') {
                                            echo '<div class="horario-item">
                                                    <b>'.$dia_semana.'</b><br>
                                                    '.$horainiciotar.' - '.$horafintar.'
                                                  </div>';
                                            $horario_col_empty = false;
                                        }
                                    }
                                    if($current_doctor != ''){
                                        if ($horario_col_empty) {
                                            echo '<p>No se encontraron horarios disponibles</p>';
                                        }
                                        echo '</div></td>';
                                        echo '<td>';
                                        if (!$horario_col_empty) {
                                            echo '<button class="login-btn btn-primary-soft btn agendar-btn" style="padding-top:11px;padding-bottom:11px;width:100%" data-docid="'.$docid.'" data-docnombre="'.$current_doctor.'" data-espnombre="'.$current_especialidad.'">Agendar cita</button>';
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
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required><br><br>
                <label for="horas">Horas disponibles:</label>
                <select id="horas" name="horas" required>
                    <option value="" disabled selected>Escoge una hora de la lista</option>
                </select><br><br>
                <button type="submit" class="login-btn btn-primary-soft btn">+ Agendar cita</button>
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

                // Set modal data
                document.getElementById("modalDocnombre").innerText = docnombre;
                document.getElementById("modalEspnombre").innerText = espnombre;
                document.getElementById("horas").innerHTML = '<option value="" disabled selected>Escoge una hora de la lista</option>';

                // Show the modal
                modal.style.display = "block";
            }
        }

        // Fetch available hours when a date is selected
        document.getElementById("fecha").addEventListener("change", function() {
            var fecha = this.value;
            var docnombre = document.getElementById("modalDocnombre").innerText;

            if (fecha) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "fetch_horarios.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById("horas").innerHTML = xhr.responseText;
                    }
                };
                xhr.send("fecha=" + fecha + "&docnombre=" + docnombre);
            }
        });
    </script>
</body>
</html>
