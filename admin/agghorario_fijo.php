<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Horarios</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
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
            if (isset($_POST['shedulesubmit'])) {
                // Recoger los datos del formulario
                $doctor_id = $_GET['id']; 
                $days_selected = $_POST['day_schedule']; // Array de días seleccionados
                $horainicioman = $_POST['horainicioman']; // Hora de inicio de mañana
                $horafinman = $_POST['horafinman']; // Hora de fin de mañana
                $horainiciotar = $_POST['horainiciotar']; // Hora de inicio de tarde
                $horafintar = $_POST['horafintar']; // Hora de fin de tarde

                // Recorrer los días seleccionados y guardar en la base de datos
                foreach ($days_selected as $day) {
                    $sql = "INSERT INTO disponibilidad_doctor (docid, dia_semana, horainicioman, horafinman, horainiciotar, horafintar)
                            VALUES ('$doctor_id', '$day', '$horainicioman', '$horafinman', '$horainiciotar', '$horafintar')";



                    // Ejecutar la consulta
                    if ($database->query($sql)) {
                        //echo "Horario para el día $day agregado correctamente.<br>";
                        $_SESSION['success_message'] = "Horario para el día $day agregado correctamente.";
                    } else {
                        //echo "Error al agregar el horario para el día $day: " . $database->error . "<br>";
                        $_SESSION['error_message'] = "Error al agregar el horario para el día $day: " . $database->error;
                    }
                }

                // Redirigir a la misma página para evitar el reenvío del formulario
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $doctor_id);
                exit; // Asegurarse de que el script se detenga después de la redirección
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
            <div style="display: flex;justify-content: center;">
                <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                    <tr>
                        <td>
                            <p style="font-size: 25px; font-weight: 500;">Horario fijo</p><br><br>
                        </td>
                    </tr>
                </table>
            </div>
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
                        <h2></h2>
                        
                        
                        <div style="display: flex;justify-content: center;">
                            <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                                <tr>
                                    <td><p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Agregar nuevo horario.</p><br></td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <form action="" method="POST" class="add-new-form">
                                            <!-- Horario semanal -->
                                            <label for="fecha" class="form-label">Horario semanal: </label><br>
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

            ?>
</body>
</html>