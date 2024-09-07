<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/styles.css">
        
    <title>Horarios</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        /* Estilos para las pestañas */
        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
        }

        /* Botones de las pestañas */
        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
        }

        /* Cambiar el color del botón de la pestaña al pasar el mouse */
        .tab button:hover {
            background-color: #ddd;
        }

        /* Color de la pestaña activa */
        .tab button.active {
            background-color: #ccc;
        }

        /* Estilo de contenido de pestañas */
        .tabcontent {
            display: none;
            padding: 16px;
            border: 1px solid #ccc;
            border-top: none;
        }
         /* Mostrar la primera pestaña por defecto */
         .tabcontent.active {
            display: block;
        }
</style>
</head>
<body>
    <?php

    //learn from w3schools.com

    session_start();

    // Mostrar mensajes de éxito o error si existen
    if (isset($_SESSION['success_message'])) {
        echo '<p style="color: green;">' . $_SESSION['success_message'] . '</p>';
        unset($_SESSION['success_message']); // Eliminar el mensaje para evitar que se muestre nuevamente
    }

    if (isset($_SESSION['error_message'])) {
        echo '<p style="color: red;">' . $_SESSION['error_message'] . '</p>';
        unset($_SESSION['error_message']); // Eliminar el mensaje para evitar que se muestre nuevamente
    }
    // Mostrar mensajes de éxito o error si existen


    if(isset($_SESSION["usuario"])){
        if(($_SESSION["usuario"])=="" or $_SESSION['usuario_rol']!='adm'){
            header("location: ../login.php");
            exit;
        }
    }else{
        header("location: ../login.php");
        exit;
    }

    //import database
    include("../conexion_db.php");

    // Procesar los datos del formulario
            if (isset($_POST['shedulesubmit'])|| isset($_POST['submitPersonalizado'])) {
                // Recoger los datos del formulario
                $doctor_id = $_GET['id']; 

                // Lógica para el horario fijo
                if (isset($_POST['shedulesubmit'])) {
                    $days_selected = $_POST['day_schedule'] ?? [];
                    $horainicioman = $_POST['horainicioman'];
                    $horafinman = $_POST['horafinman'];
                    $horainiciotar = $_POST['horainiciotar'];
                    $horafintar = $_POST['horafintar'];

                    foreach ($days_selected as $day) {
                        $sql = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar)
                                VALUES ('$doctor_id', '$day', '$horainicioman', '$horafinman', '$horainiciotar', '$horafintar')";
            
                        if ($database->query($sql)) {
                            $_SESSION['success_message'] = "Horario para el día $day agregado correctamente.";
                        } else {
                            $_SESSION['error_message'] = "Error al agregar el horario para el día $day: " . $database->error;
                        }
                    }
                }

                // Lógica para el horario personalizado
                if (isset($_POST['submitPersonalizado'])) {
                    $dias = $_POST['dias'] ?? [];

                    if (!empty($dias)) {
                        foreach ($dias as $dia) {
                            $horainicioman = $_POST['horainicioman_' . $dia] ?? null;
                            $horafinman = $_POST['horafinman_' . $dia] ?? null;
                            $horainiciotar = $_POST['horainiciotar_' . $dia] ?? null;
                            $horafintar = $_POST['horafintar_' . $dia] ?? null;

                            $sql = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar)
                                    VALUES ('$doctor_id', '$dia', '$horainicioman', '$horafinman', '$horainiciotar', '$horafintar')";

                            if ($database->query($sql)) {
                                $_SESSION['success_message'] = "Horario personalizado para el día $dia agregado correctamente.";
                            } else {
                                $_SESSION['error_message'] = "Error al agregar el horario personalizado para el día $dia: " . $database->error;
                            }
                        }
                    }
                }

                 // Redirigir después del procesamiento
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $doctor_id);
                exit;
            }
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
                                    <p class="profile-title">Administrador</p>
                                    <!-- <p class="profile-subtitle">admin@edoc.com</p> -->
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <a href="../logout.php" ><input type="button" value="Cerrar sesión" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                    </table>
                    </td>
                
                </tr>
                <!-- <tr class="menu-row" >
                    <td class="menu-btn menu-icon-dashbord" >
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></a></div></a>
                    </td>
                </tr> -->
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor ">
                        <a href="doctores.php" class="non-style-link-menu "><div><p class="menu-text">Doctores</p></a></div>
                    </td>
                </tr>
                
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-patient">
                        <a href="pacientes.php" class="non-style-link-menu"><div><p class="menu-text">Pacientes</p></a></div>
                    </td>
                </tr>

                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment menu-active menu-icon-appoinment-active">
                        <a href="citas.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Citas agendadas</p></a></div>
                    </td>
                </tr>

                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-schedule ">
                        <a href="horarios.php" class="non-style-link-menu"><div><p class="menu-text">Horarios disponibles</p></div></a>
                    </td>
                </tr>

            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                    <a href="horarios.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Agregar Horario</p>
                                           
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php 

                        date_default_timezone_set('Asia/Kolkata');

                        $today = date('Y-m-d');
                        echo $today;

                        $list110 = $database->query("select  * from  horarios;");

                        ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>


                </tr>
                
                    
                    
    </div>
    
    </div>

    <?php
    
    if($_GET){
        $id=$_GET["id"];
        
            $sqlmain= "select * from doctor where docid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $docnombre=$row["docnombre"];
            
            $espe=$row["especialidades"];
            
            $especial_res= $database->query("select espnombre from especialidades where id='$espe'");
            $especial_array= $especial_res->fetch_assoc();
            $especial_name=$especial_array["espnombre"];
            


            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2></h2>
                        
                        
                        <div style="display: flex;justify-content: center;">
                        <table width="60%" class="sub-table scrolldown add-doc-form-container" border="0">
                            
                            <tr>

                                <td class="label-td" colspan="2">
                                    <label for="espec" class="form-label">Especialidad: </label>
                                </td>
                                <td class="label-td" colspan="2">
                                    '.$especial_name.'<br><br>
                                </td>
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Doctor: </label>
                                </td>
                                <td class="label-td" colspan="2">
                                    '.$docnombre.'<br><br>
                                </td>

                            </tr>

                        </table>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>

            
            ';
        }
    
            ?>

            <!-- Contenedor de las pestañas -->
            <div class="tab">
                <button class="tablinks" onclick="openTab(event, 'HorarioFijo')" id="defaultOpen">Horario Fijo</button>
                <button class="tablinks" onclick="openTab(event, 'HorarioPersonalizado')" >Horario Personalizado</button>
            </div>
          
            
            <!-- Contenido de Horario Fijo -->
            <div id="HorarioFijo" class="tabcontent">
                <h3>Horario Fijo</h3>
                
                <?php

            if ($_GET) {
                $id = $_GET["id"];
                
                // Consultar al doctor usando el ID del doctor
                $sqlmain = "SELECT * FROM doctor WHERE docid='$id'";
                $result = $database->query($sqlmain);
                $row = $result->fetch_assoc();
                $docnombre = $row["docnombre"];
                
                $espe = $row["especialidades"];
                $especial_res = $database->query("SELECT espnombre FROM especialidades WHERE id='$espe'");
                $especial_array = $especial_res->fetch_assoc();
                $especial_name = $especial_array["espnombre"];
                
                // Mostrar el formulario con los días y horas
                echo '
                <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        
                        <div style="display: flex;justify-content: center;">
                            <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                                
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <form action="" method="POST" class="add-new-form">
                                            <input type="checkbox" id="checkboxLunes" name="day_schedule[]" value="Lunes"> <label for="checkboxLunes">Lunes</label><br>
                                            <input type="checkbox" id="checkboxMartes" name="day_schedule[]" value="Martes"> <label for="checkboxMartes">Martes</label><br>
                                            <input type="checkbox" id="checkboxMiercoles" name="day_schedule[]" value="Miercoles"> <label for="checkboxMiercoles">Miércoles</label><br>
                                            <input type="checkbox" id="checkboxJueves" name="day_schedule[]" value="Jueves"> <label for="checkboxJueves">Jueves</label><br>
                                            <input type="checkbox" id="checkboxViernes" name="day_schedule[]" value="Viernes"> <label for="checkboxViernes">Viernes</label><br>
                                            <input type="checkbox" id="checkboxSabado" name="day_schedule[]" value="Sabado"> <label for="checkboxSabado">Sábado</label><br>
                                            <input type="checkbox" id="checkboxDomingo" name="day_schedule[]" value="Domingo"> <label for="checkboxDomingo">Domingo</label><br><br>

                                            <!-- Horario de mañana -->
                                            <label for="horainicioman" class="form-label">Horario de mañana: </label>
                                            <input type="time" name="horainicioman" class="input-text" placeholder="Hora de inicio" >
                                            <span class="col-auto"> - </span>
                                            <input type="time" name="horafinman" class="input-text" placeholder="Hora de fin" ><br><br>

                                            <!-- Horario de tarde -->
                                            <label for="horainiciotar" class="form-label">Horario de tarde: </label>
                                            <input type="time" name="horainiciotar" class="input-text" placeholder="Hora de inicio" >
                                            <span class="col-auto"> - </span>
                                            <input type="time" name="horafintar" class="input-text" placeholder="Hora de fin" ><br><br>

                                            <!-- Botón para agregar el horario -->
                                            <input type="submit" value="Agregar horario" class="login-btn btn-primary btn" name="shedulesubmit">
                                        </form>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </center>
                    <br><br>
                </div>
                </div>
                ';
            }
            // Procesar los datos del formulario de horario personalizado
            if (isset($_POST['submitPersonalizado'])) {
                $doctor_id = $_GET['id'];  // ID del doctor obtenido de la URL
                $dias = $_POST['dias'] ?? [];  // Array de días seleccionados

                if (!empty($dias)) {
                    foreach ($dias as $dia) {
                        // Recoger horarios de mañana y tarde de cada día
                        $horainicioman = $_POST['horainicioman_' . $dia] ?? null;
                        $horafinman = $_POST['horafinman_' . $dia] ?? null;
                        $horainiciotar = $_POST['horainiciotar_' . $dia] ?? null;
                        $horafintar = $_POST['horafintar_' . $dia] ?? null;

                        // Insertar el horario solo si al menos un horario está definido (mañana o tarde)
                        if ($horainicioman || $horainiciotar) {
                            $sql = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar)
                                    VALUES ('$doctor_id', '$dia', '$horainicioman', '$horafinman', '$horainiciotar', '$horafintar')";

                            // Ejecutar la consulta
                            if ($database->query($sql)) {
                                $_SESSION['success_message'] = "Horario personalizado agregado correctamente para el día $dia.";
                            } else {
                                $_SESSION['error_message'] = "Error al agregar el horario personalizado para el día $dia: " . $database->error;
                            }
                        } else {
                            $_SESSION['error_message'] = "Debe ingresar al menos un horario para el día $dia.";
                        }
                    }

                    // Redirigir para evitar el reenvío del formulario
                    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $doctor_id);
                    exit();
                } else {
                    $_SESSION['error_message'] = "Por favor seleccione al menos un día.";
                }
            }

            ?>
            
            <div id="HorarioPersonalizado" class="tabcontent">
                <h3>Horario Personalizado</h3>
                
                <!-- Formulario de Horario Personalizado -->
                <form action="" method="POST">
                    <table border="0" width="100%">
                        <tr>
                            <th>Día</th>
                            <th>Horario de Mañana</th>
                            <th>Horario de Tarde</th>
                        </tr>
                        <?php
                        $dias_semana = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"];
                        foreach ($dias_semana as $dia) {
                            echo '<tr>';
                            echo '<td><input type="checkbox" name="dias[]" value="'.$dia.'"> '.$dia.'</td>';
                            echo '<td>Inicio: <input type="time" name="horainicioman_'.$dia.'"> Fin: <input type="time" name="horafinman_'.$dia.'"></td>';
                            echo '<td>Inicio: <input type="time" name="horainiciotar_'.$dia.'"> Fin: <input type="time" name="horafintar_'.$dia.'"></td>';
                            echo '</tr>';
                        }
                        ?>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                    </table>
                    <input type="submit" value="Agregar horario" name="submitPersonalizado">
                </form>
            </div>

            </div>
            <script>
                // Función para abrir pestañas
                function openTab(evt, tabName) {
                    var i, tabcontent, tablinks;
                    
                    // Ocultar todas las pestañas
                    tabcontent = document.getElementsByClassName("tabcontent");
                    for (i = 0; i < tabcontent.length; i++) {
                        tabcontent[i].style.display = "none";
                    }

                    // Remover clase "active" de todos los botones
                    tablinks = document.getElementsByClassName("tablinks");
                    for (i = 0; i < tablinks.length; i++) {
                        tablinks[i].className = tablinks[i].className.replace(" active", "");
                    }

                    // Mostrar el contenido de la pestaña seleccionada
                    document.getElementById(tabName).style.display = "block";
                    //document.getElementById("HorarioPersonalizado").style.display = "block"; // Forzar la visibilidad del contenido

                    // Añadir la clase "active" al botón de la pestaña seleccionada
                    evt.currentTarget.className += " active";

                    console.log("Pestaña mostrada:", tabName);  // Verifica si esta línea se ejecuta correctamente

                }

                // Ejecutar esta función solo cuando todo el DOM esté cargado
                document.addEventListener("DOMContentLoaded", function() {
                    // Abrir la pestaña por defecto (Horario Fijo) al cargar la página
                    document.getElementById("defaultOpen").click();
                });
            </script>


            
</body>
</html>