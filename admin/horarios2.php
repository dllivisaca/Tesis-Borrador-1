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
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Gestor de horarios disponibles</p>
                                           
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
                        Fecha:
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
                        FROM disponibilidad_doctor 
                        INNER JOIN doctor ON disponibilidad_doctor.docid = doctor.docid";
                        

                        // Aplica el filtro por doctor si se selecciona uno
                        if (!empty($sqlpt2)) {
                            $sqlmain .= " WHERE $sqlpt2";
                        }

                    } else {
                        // Consulta por defecto si no se ha seleccionado ningún filtro
                        $sqlmain = "SELECT doctor.docid, doctor.docnombre, doctor.especialidades, disponibilidad_doctor.dia_semana, 
                                    disponibilidad_doctor.horainicioman, disponibilidad_doctor.horafinman, 
                                    disponibilidad_doctor.horainiciotar, disponibilidad_doctor.horafintar 
                                    FROM disponibilidad_doctor 
                                    INNER JOIN doctor ON disponibilidad_doctor.docid = doctor.docid";
                    }

                        
                        //echo $sqlmain;

                        
                        
                        //
                    


                ?>
                  
                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                                <th class="table-headin">
                                    
                                
                                 Nombre del doctor
                                
                                </th>
                                
                                <th class="table-headin">
                                    Especialidad
                                </th>
                                <th class="table-headin">
                                    
                                    Horario disponible
                                    
                                </th>
                                
                                
                                <th class="table-headin">
                                    
                                    Acciones
                                    
                                </tr>
                        </thead>
                        <tbody>
                        
                            <?php

                                // Ejecutar la consulta
                                $result = $database->query($sqlmain);
                               

                                if ($result->num_rows == 0) {
                                    echo '<tr><td colspan="4">
                                        <center>
                                        <img src="../img/notfound.svg" width="25%">
                                        <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No se encontraron horarios disponibles!</p>
                                        <a class="non-style-link" href="horarios2.php"><button class="login-btn btn-primary-soft btn" style="margin-left:20px;">&nbsp; Ver todos los horarios &nbsp;</button></a>
                                        </center>
                                    </td></tr>';
                                } else {
                                    // Mostrar resultados
                                    for ($x = 0; $x < $result->num_rows; $x++) {
                                        $row = $result->fetch_assoc();
                                        $docid = $row['docid'];
                                        $docnombre = $row['docnombre'];
                                        $espe = $row['especialidades'];
                                        $dia_semana = $row['dia_semana'] ?? 'N/A';
                                        $horainicioman = $row['horainicioman'] ?? '00:00:00';
                                        $horafinman = $row['horafinman'] ?? '00:00:00';
                                        $horainiciotar = $row['horainiciotar'] ?? '00:00:00';
                                        $horafintar = $row['horafintar'] ?? '00:00:00';
                                
                                        // Obtener el nombre de la especialidad
                                        $especial_res = $database->query("SELECT espnombre FROM especialidades WHERE id='$espe'");
                                        $especial_array = $especial_res->fetch_assoc();
                                        $especial_name = $especial_array['espnombre'] ?? 'N/A';
                                
                                        // Formatear los horarios
                                        $horario_disponible = '';
                                        if ($horainicioman != '00:00:00' && $horafinman != '00:00:00') {
                                            $horario_disponible .= 'Mañana: ' . substr($horainicioman, 0, 5) . ' - ' . substr($horafinman, 0, 5) . '<br>';
                                        }
                                        if ($horainiciotar != '00:00:00' && $horafintar != '00:00:00') {
                                            $horario_disponible .= 'Tarde: ' . substr($horainiciotar, 0, 5) . ' - ' . substr($horafintar, 0, 5);
                                        }
                                
                                        if (empty($horario_disponible)) {
                                            $horario_disponible = 'N/A';
                                        }
                                
                                        // Mostrar los resultados en la tabla
                                        echo '<tr>
                                            <td>' . $docnombre . '</td>
                                            <td>' . $especial_name . '</td>
                                            <td style="text-align:center;">' . $dia_semana . '<br>' . $horario_disponible . '</td>
                                            <td>
                                                <div style="display:flex;justify-content: center;">
                                                    <a href="agghorario_fijo.php?id=' . $docid . '" class="non-style-link">
                                                        <button class="btn-primary-soft btn button-icon btn-view" style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">Agregar horario</button>
                                                    </a>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <a href="?action=drop&id=' . $docid . '" class="non-style-link">
                                                        <button class="btn-primary-soft btn button-icon btn-delete" style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">Editar</button>
                                                    </a>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <a href="agendar.php?id=' . $docid . '" class="non-style-link">
                                                        <button class="login-btn btn-primary-soft btn" style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">Eliminar</button>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>';
                                    
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
    <?php
    
    if($_GET){
        $id=$_GET["id"];
        $action=$_GET["action"];
        if($action=='agregar_horario'){
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
                        <a class="close" href="horarios2.php">&times;</a>
                        <div class="content">
                            eDoc Web App<br>
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                        
                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">View Details.</p><br><br>
                                </td>
                            </tr>
                            
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Name: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.$docnombre.'<br><br>
                                </td>
                                
                            </tr>
                            
                           
                            
                           
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="espec" class="form-label">Especialidades: </label>
                                    
                                </td>
                            </tr>
                            <tr>
                            <td class="label-td" colspan="2">
                            '.$especial_name.'<br><br>
                            </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="horarios2.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                
                                    
                                </td>
                
                            </tr>
                           

                        </table>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>
            ';
        }elseif($action=='session-added'){
            $titleget=$_GET["title"];
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                    <br><br>
                        <h2>Session Placed.</h2>
                        <a class="close" href="horarios2.php">&times;</a>
                        <div class="content">
                        '.substr($titleget,0,40).' was scheduled.<br><br>
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        
                        <a href="horarios2.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
                        <br><br><br><br>
                        </div>
                    </center>
            </div>
            </div>
            ';
        }elseif($action=='drop'){
            $nameget=$_GET["name"];
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2>Are you sure?</h2>
                        <a class="close" href="horarios2.php">&times;</a>
                        <div class="content">
                            You want to delete this record<br>('.substr($nameget,0,40).').
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <a href="borrar_horario.php?id='.$id.'" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"<font class="tn-in-text">&nbsp;Yes&nbsp;</font></button></a>&nbsp;&nbsp;&nbsp;
                        <a href="horarios2.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font></button></a>

                        </div>
                    </center>
            </div>
            </div>
            '; 
        }elseif($action=='view'){
            $sqlmain= "select horarios.horarioid,horarios.titulo,doctor.docnombre,horarios.horariofecha,horarios.horariohora from horarios inner join doctor on horarios.docid=doctor.docid  where  horarios.horarioid=$id";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $docnombre=$row["docnombre"];
            $horarioid=$row["horarioid"];
            $titulo=$row["titulo"];
            $horariofecha=$row["horariofecha"];
            $horariohora=$row["horariohora"];
            
           
            
            $sqlmain12= "select * from citas inner join paciente on paciente.pacid=citas.pacid inner join horarios on horarios.horarioid=citas.horarioid where horarios.horarioid=$id;";
            $result12= $database->query($sqlmain12);
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup" style="width: 70%;">
                    <center>
                        <h2></h2>
                        <a class="close" href="horarios2.php">&times;</a>
                        <div class="content">
                            
                            
                        </div>
                        <div class="abc scroll" style="display: flex;justify-content: center;">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                        
                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">View Details.</p><br><br>
                                </td>
                            </tr>
                            
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Título de la: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.$titulo.'<br><br>
                                </td>
                                
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Email" class="form-label">Doctor of this session: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$docnombre.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="ci" class="form-label">Scheduled Date: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$horariofecha.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Tele" class="form-label">Scheduled Time: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$horariohora.'<br><br>
                                </td>
                            </tr>
                                                        
                            <tr>
                            <td colspan="4">
                                <center>
                                 <div class="abc scroll">
                                 <table width="100%" class="sub-table scrolldown" border="0">
                                 <thead>
                                 <tr>   
                                        <th class="table-headin">
                                             Patient ID
                                         </th>
                                         <th class="table-headin">
                                             Patient name
                                         </th>
                                         <th class="table-headin">
                                             
                                             Appointment number
                                             
                                         </th>
                                        
                                         
                                         <th class="table-headin">
                                             Patient Telephone
                                         </th>
                                         
                                 </thead>
                                 <tbody>';
                                 
                
                
                                         
                                         $result= $database->query($sqlmain12);
                
                                         if($result->num_rows==0){
                                             echo '<tr>
                                             <td colspan="7">
                                             <br><br><br><br>
                                             <center>
                                             <img src="../img/notfound.svg" width="25%">
                                             
                                             <br>
                                             <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We  couldnt find anything related to your keywords !</p>
                                             <a class="non-style-link" href="citas.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Appointments &nbsp;</font></button>
                                             </a>
                                             </center>
                                             <br><br><br><br>
                                             </td>
                                             </tr>';
                                             
                                         }
                                         else{
                                         for ( $x=0; $x<$result->num_rows;$x++){
                                             $row=$result->fetch_assoc();
                                             $citanum=$row["citanum"];
                                             $pacid=$row["pacid"];
                                             $pnombre=$row["pnombre"];
                                             $ptel=$row["ptel"];
                                             
                                             echo '<tr style="text-align:center;">
                                                <td>
                                                '.substr($pacid,0,15).'
                                                </td>
                                                 <td style="font-weight:600;padding:25px">'.
                                                 
                                                 substr($pnombre,0,25)
                                                 .'</td >
                                                 <td style="text-align:center;font-size:23px;font-weight:500; color: var(--btnnicetext);">
                                                 '.$citanum.'
                                                 
                                                 </td>
                                                 <td>
                                                 '.substr($ptel,0,25).'
                                                 </td>
                                                 
                                                 
                
                                                 
                                             </tr>';
                                             
                                         }
                                     }
                                          
                                     
                
                                    echo '</tbody>
                
                                 </table>
                                 </div>
                                 </center>
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
}
        
    ?>
    </div>

</body>
</html>