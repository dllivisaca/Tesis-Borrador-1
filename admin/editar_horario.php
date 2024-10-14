<!DOCTYPE html>
<html lang="en">
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

    if(isset($_SESSION["usuario"])){
        if(($_SESSION["usuario"])=="" or $_SESSION['usuario_rol']!='adm'){
            header("location: ../login.php");
        }
    }else{
        header("location: ../login.php");
    }

    //import database
    include("../conexion_db.php");
    
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
                        <a href="horarios2.php" class="non-style-link-menu"><div><p class="menu-text">Horarios disponibles</p></div></a>
                    </td>
                </tr>

            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                    <a href="horarios2.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Editar Horario</p>
                                           
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

                        

                        ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>


                </tr>
               
                
                <tr>
                    <!-- <td colspan="4" style="padding-top:10px;width: 100%;" >
                    
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">Todos los horarios(<?php echo $list110->num_rows; ?>)</p>
                    </td> -->
                    
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:0px;width: 100%;" >
                        <center>
                        <table class="filter-container" border="0" >
                        <tr>
                           <td width="10%">

                           </td> 
                        <td width="5%" style="text-align: center;">
                        Especialidad:
                        </td>
                        <td width="30%">
                        <form action="" method="post">
                            
                            <input type="date" name="horariofecha" id="date" class="input-text filter-container-items" style="margin: 0;width: 95%;">

                        </td>
                        <td width="5%" style="text-align: center;">
                        Doctor:
                        </td>
                        <td width="30%">
                        <select name="docid" id="" class="box filter-container-items" style="width:90% ;height: 37px;margin: 0;" >
                            <option value="" disabled selected hidden>Escoge un doctor de la lista</option><br/>
                                
                            <?php 
                            
                                $list11 = $database->query("select  * from  doctor order by docnombre asc;");

                                for ($y=0;$y<$list11->num_rows;$y++){
                                    $row00=$list11->fetch_assoc();
                                    $sn=$row00["docnombre"];
                                    $id00=$row00["docid"];
                                    echo "<option value=".$id00.">$sn</option><br/>";
                                };


                                ?>

                        </select>
                    </td>
                    <td width="12%">
                        <input type="submit"  name="filter" value=" Filter" class=" btn-primary-soft btn button-icon btn-filter"  style="padding: 15px; margin :0;width:100%">
                        </form>
                    </td>

                    </tr>
                            </table>

                        </center>
                    </td>
                    
                </tr>
                
                <?php
                // Definir la función globalmente para que esté disponible en cualquier contexto
                function ordenarDiasSemana($a, $b) {
                    $ordenDias = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"];
                    $posA = array_search($a['dia_semana'], $ordenDias);
                    $posB = array_search($b['dia_semana'], $ordenDias);
                    return $posA - $posB;
                }

                    if($_POST){
                        //print_r($_POST);                    

                        $sqlpt2="";
                        if(!empty($_POST["docid"])){
                            $docid=$_POST["docid"];
                            $sqlpt2=" doctor.docid=$docid ";
                        }

                        
                                            
                        // Consulta SQL para obtener los horarios disponibles de la tabla `disponibilidad_doctor`
                        $sqlmain = "SELECT doctor.docid, doctor.docnombre, doctor.especialidades, disponibilidad_doctor.dia_semana, 
                        disponibilidad_doctor.horainicioman, disponibilidad_doctor.horafinman, 
                        disponibilidad_doctor.horainiciotar, disponibilidad_doctor.horafintar 
                        FROM doctor 
                        LEFT JOIN disponibilidad_doctor ON doctor.docid = disponibilidad_doctor.docid";

                        // Aplica el filtro por doctor si se selecciona uno
                        if (!empty($sqlpt2)) {
                        $sqlmain .= " WHERE $sqlpt2";
                        }

                    } else {
                        // Consulta por defecto si no se ha seleccionado ningún filtro
                        $sqlmain = "SELECT doctor.docid, doctor.docnombre, doctor.especialidades, disponibilidad_doctor.dia_semana, disponibilidad_doctor.horainicioman, disponibilidad_doctor.horafinman, disponibilidad_doctor.horainiciotar, disponibilidad_doctor.horafintar 
                        FROM doctor 
                        LEFT JOIN disponibilidad_doctor ON doctor.docid = disponibilidad_doctor.docid";
                    }

                        
                        //echo $sqlmain;

                        
                        
                        //
                    


                ?>
                  
                
                        
                        
            </table>
        </div>
    </div>
    <?php

if ($_GET) {
    $docid = $_GET['id'];

    // Consultar los datos del doctor y su disponibilidad actual
    $sql = "SELECT doctor.docnombre, doctor.especialidades, disponibilidad_doctor.* 
            FROM doctor 
            LEFT JOIN disponibilidad_doctor 
            ON doctor.docid = disponibilidad_doctor.docid 
            WHERE doctor.docid = '$docid'";

    $result = $database->query($sql);
    $doctor = $result->fetch_assoc();

    // Obtener especialidad del doctor
    $especialidad_res = $database->query("SELECT espnombre FROM especialidades WHERE id = '{$doctor['especialidades']}'");
    $especialidad = $especialidad_res->fetch_assoc()['espnombre'];
}
?>
    <div class="container">
        <h2>Editar Horario del Doctor</h2>
        <form action="update_horario.php" method="POST">
            <input type="hidden" name="docid" value="<?php echo $docid; ?>">

            <label for="docnombre">Nombre del Doctor:</label>
            <input type="text" id="docnombre" name="docnombre" value="<?php echo $doctor['docnombre']; ?>" readonly>

            <label for="especialidad">Especialidad:</label>
            <input type="text" id="especialidad" name="especialidad" value="<?php echo $especialidad; ?>" readonly>

            <label for="dia_semana">Día de la semana:</label>
            <select name="dia_semana" id="dia_semana">
                <option value="Lunes" <?php if ($doctor['dia_semana'] == "Lunes") echo "selected"; ?>>Lunes</option>
                <option value="Martes" <?php if ($doctor['dia_semana'] == "Martes") echo "selected"; ?>>Martes</option>
                <option value="Miércoles" <?php if ($doctor['dia_semana'] == "Miércoles") echo "selected"; ?>>Miércoles</option>
                <option value="Jueves" <?php if ($doctor['dia_semana'] == "Jueves") echo "selected"; ?>>Jueves</option>
                <option value="Viernes" <?php if ($doctor['dia_semana'] == "Viernes") echo "selected"; ?>>Viernes</option>
                <option value="Sábado" <?php if ($doctor['dia_semana'] == "Sábado") echo "selected"; ?>>Sábado</option>
                <option value="Domingo" <?php if ($doctor['dia_semana'] == "Domingo") echo "selected"; ?>>Domingo</option>
            </select>

            <label for="horainicioman">Hora Inicio Mañana:</label>
            <input type="time" id="horainicioman" name="horainicioman" value="<?php echo substr($doctor['horainicioman'], 0, 5); ?>">

            <label for="horafinman">Hora Fin Mañana:</label>
            <input type="time" id="horafinman" name="horafinman" value="<?php echo substr($doctor['horafinman'], 0, 5); ?>">

            <label for="horainiciotar">Hora Inicio Tarde:</label>
            <input type="time" id="horainiciotar" name="horainiciotar" value="<?php echo substr($doctor['horainiciotar'], 0, 5); ?>">

            <label for="horafintar">Hora Fin Tarde:</label>
            <input type="time" id="horafintar" name="horafintar" value="<?php echo substr($doctor['horafintar'], 0, 5); ?>">

            <button type="submit">Guardar Cambios</button>
        </form>
    </div>

</body>
</html>




