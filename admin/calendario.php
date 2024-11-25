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
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-dashbord" >
                        <a href="calendario.php" class="non-style-link-menu"><div><p class="menu-text">Calendario</p></a></div></a>
                    </td>
                </tr>
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
                        <a href="calendario.php" class="non-style-link-menu"><div><p class="menu-text">Horarios disponibles</p></div></a>
                    </td>
                </tr>

            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                    <a href="calendario.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
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

                        $list110 = $database->query("select  * from  horarios;");

                        ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>


                </tr>
               
                <tr>
                    <td colspan="4" >
                        <div style="display: flex;margin-top: 40px;">
                        <div class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49);margin-top: 5px;">Agrega un horario</div>
                        <a href="?action=agregar_horario2&id=none&error=0" class="non-style-link"><button  class="login-btn btn-primary btn button-icon"  style="margin-left:25px;background-image: url('../img/icons/add.svg');">Agrega un horario nuevo</font></button>
                        </a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:10px;width: 100%;" >
                    
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">Todos los horarios(<?php echo $list110->num_rows; ?>)</p>
                    </td>
                    
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
                        $sqlpt1="";
                        if(!empty($_POST["horariofecha"])){
                            $horariofecha=$_POST["horariofecha"];
                            $sqlpt1=" horarios.horariofecha='$horariofecha' ";
                        }


                        $sqlpt2="";
                        if(!empty($_POST["docid"])){
                            $docid=$_POST["docid"];
                            $sqlpt2=" doctor.docid=$docid ";
                        }
                        //echo $sqlpt2;
                        //echo $sqlpt1;
                        $sqlmain= "select horarios.horarioid,horarios.titulo,doctor.docnombre,horarios.horariofecha,horarios.horariohora from horarios inner join doctor on horarios.docid=doctor.docid ";
                        $sqllist=array($sqlpt1,$sqlpt2);
                        $sqlkeywords=array(" where "," and ");
                        $key2=0;
                        foreach($sqllist as $key){

                            if(!empty($key)){
                                $sqlmain.=$sqlkeywords[$key2].$key;
                                $key2++;
                            };
                        };
                        //echo $sqlmain;

                        
                        
                        //
                    }else{
                        $sqlmain= "select horarios.horarioid,horarios.titulo,doctor.docnombre,horarios.horariofecha,horarios.horariohora from horarios inner join doctor on horarios.docid=doctor.docid  order by horarios.horariofecha desc";

                    }



                ?>
                  
                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                                <th class="table-headin">
                                    
                                
                                Titulo
                                
                                </th>
                                
                                <th class="table-headin">
                                    Doctor
                                </th>
                                <th class="table-headin">
                                    
                                    Fecha y hora
                                    
                                </th>
                                
                                
                                <th class="table-headin">
                                    
                                    Acciones
                                    
                                </tr>
                        </thead>
                        <tbody>
                        
                            <?php

                                
                                $result= $database->query($sqlmain);

                                if($result->num_rows==0){
                                    echo '<tr>
                                    <td colspan="4">
                                    <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We  couldnt find anything related to your keywords !</p>
                                    <a class="non-style-link" href="calendario.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Ver todos los horarios &nbsp;</font></button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                    
                                }
                                else{
                                for ( $x=0; $x<$result->num_rows;$x++){
                                    $row=$result->fetch_assoc();
                                    $horarioid=$row["horarioid"];
                                    $titulo=$row["titulo"];
                                    $docnombre=$row["docnombre"];
                                    $horariofecha=$row["horariofecha"];
                                    $horariohora=$row["horariohora"];
                                    
                                    echo '<tr>
                                        <td> &nbsp;'.
                                        substr($titulo,0,30)
                                        .'</td>
                                        <td>
                                        '.substr($docnombre,0,20).'
                                        </td>
                                        <td style="text-align:center;">
                                            '.substr($horariofecha,0,10).' '.substr($horariohora,0,5).'
                                        </td>
                                        

                                        <td>
                                        <div style="display:flex;justify-content: center;">
                                        
                                        <a href="?action=view&id='.$horarioid.'" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-view"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">View</font></button></a>
                                       &nbsp;&nbsp;&nbsp;
                                       <a href="?action=drop&id='.$horarioid.'&name='.$titulo.'" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-delete"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Remove</font></button></a>
                                       &nbsp;&nbsp;&nbsp;
                                       
                                       <a href="agendar.php?id='.$horarioid.'" class="non-style-link"><button  class="login-btn btn-primary-soft btn "  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Book Now</font></button></a>

                                       
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
        if($action=='agregar_horario2'){

            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                    
                    
                        <a class="close" href="calendario.php">&times;</a> 
                        <div style="display: flex;justify-content: center;">
                        <div class="abc">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                        <tr>
                                <td class="label-td" colspan="2">'.
                                   ""
                                
                                .'</td>
                            </tr>

                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Agregar nuevo horario.</p><br>
                                </td>
                            </tr>
                            
                            
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                <form action="agregar_horario2.php" method="POST" class="add-new-form">
                                    <label for="docid" class="form-label">Selecciona a un doctor: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <select name="docid" id="" class="box" >
                                    <option value="" disabled selected hidden>Escoge al doctor dentro de la lista</option><br/>';
                                        
        
                                        $list11 = $database->query("select  * from  doctor order by docnombre asc;");
        
                                        for ($y=0;$y<$list11->num_rows;$y++){
                                            $row00=$list11->fetch_assoc();
                                            $sn=$row00["docnombre"];
                                            $id00=$row00["docid"];
                                            echo "<option value=".$id00.">$sn</option><br/>";
                                        };        
                                        
                        echo     '       </select><br><br>
                                </td>
                            </tr>
                            
                            
                            
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="fecha" class="form-label">Horario semanal: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    
                                   <div class="form-group">
                                            <div class="icheck-primary">
                                                <input type="checkbox" id="checkboxLunes" name="day_schedule[]" value="Lunes">
                                                <label for="checkboxLunes">Lunes</label>
                                            </div>
                                    </div>
                                    <div class="form-group">
                                            <div class="icheck-primary">
                                                <input type="checkbox" id="checkboxMartes" name="day_schedule[]" value="Martes">
                                                <label for="checkboxMartes">Martes</label>
                                            </div>
                                    </div>
                                    <div class="form-group">
                                            <div class="icheck-primary">
                                                <input type="checkbox" id="checkboxMiercoles" name="day_schedule[]" value="Miercoles">
                                                <label for="checkboxMiercoles">Miercoles</label>
                                            </div>
                                    </div>
                                    <div class="form-group">
                                            <div class="icheck-primary">
                                                <input type="checkbox" id="checkboxJueves" name="day_schedule[]" value="Jueves">
                                                <label for="checkboxJueves">Jueves</label>
                                            </div>
                                    </div>
                                    <div class="form-group">
                                            <div class="icheck-primary">
                                                <input type="checkbox" id="checkboxViernes" name="day_schedule[]" value="Viernes">
                                                <label for="checkboxViernes">Viernes</label>
                                            </div>
                                    </div>
                                    <div class="form-group">
                                            <div class="icheck-primary">
                                                <input type="checkbox" id="checkboxSabado" name="day_schedule[]" value="Sabado">
                                                <label for="checkboxSabado">Sabado</label>
                                            </div>
                                    </div>
                                    <div class="form-group">
                                            <div class="icheck-primary">
                                                <input type="checkbox" id="checkboxDomingo" name="day_schedule[]" value="Domingo">
                                                <label for="checkboxDomingo">Domingo</label>
                                            </div>
                                    </div>                                        
                                        
                                </td>

                                                               
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="hora" class="form-label">Horario de mañana: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <!-- Primer campo de selección de hora -->
                                    <input type="time" name="horainicioman" class="input-text" placeholder="Hora de inicio" required>
                                    <span class="col-auto"> - </span>
                                    <!-- Segundo campo de selección de hora, separado por un espacio -->
                                    <input type="time" name="horafinman" class="input-text" placeholder="Hora de fin" required>
                                </td>
                            </tr>

                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="hora" class="form-label">Horario de tarde: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <!-- Primer campo de selección de hora -->
                                    <input type="time" name="horainiciotar" class="input-text" placeholder="Hora de inicio" required>
                                    <span class="col-auto"> - </span>
                                    <!-- Segundo campo de selección de hora, separado por un espacio -->
                                    <input type="time" name="horafintar" class="input-text" placeholder="Hora de fin" required>
                                </td>
                            </tr>
                           
                            <tr>
                                <td colspan="2">
                                    <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                
                                    <input type="submit" value="Place this Session" class="login-btn btn-primary btn" name="shedulesubmit">
                                </td>
                
                            </tr>
                           
                            </form>
                            </tr>
                        </table>
                        </div>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>
            ';
        }elseif($action=='session-added'){
            // $titleget=$_GET["title"];
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                    <br><br>
                        <h2>Session Placed.</h2>
                        <a class="close" href="calendario.php">&times;</a>
                        <div class="content">
                        Session was scheduled.<br><br>
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        
                        <a href="calendario.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
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
                        <a class="close" href="calendario.php">&times;</a>
                        <div class="content">
                            You want to delete this record<br>('.substr($nameget,0,40).').
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <a href="borrar_horario.php?id='.$id.'" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"<font class="tn-in-text">&nbsp;Yes&nbsp;</font></button></a>&nbsp;&nbsp;&nbsp;
                        <a href="calendario.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font></button></a>

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
                        <a class="close" href="calendario.php">&times;</a>
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

<div class="col-lg-12">
	<div class="card card-outline card-primary">
		<div class="card-header">
			<h5 class="card-title">Clinic Schedule Settings</h5>
		</div>
		<div class="card-body">
			<form action="" id="schedule_settings">
				<div id="msg" class="form-group"></div>
                <div class="row">
                <div class="col-lg-6">
                <div class="form-group">
                    <label for="" class="control-label">Weekly Schedule</label><br>
                    <div class="icheck-primary">
                        <input type="checkbox" id="checkboxPrimary1" name="day_schedule[]" value='Sunday' <?php echo isset($meta['day_schedule']) && in_array("Sunday",explode(",",$meta['day_schedule'])) ? "checked" : ''  ?>>
                        <label for="checkboxPrimary1">
                            Sunday
                        </label>
                    </div>
                    <div class="icheck-primary">
                        <input type="checkbox" id="checkboxPrimary2" name="day_schedule[]" value='Monday'  <?php echo isset($meta['day_schedule']) && in_array("Monday",explode(",",$meta['day_schedule'])) ? "checked" : ''  ?>>
                        <label for="checkboxPrimary2">
                            Monday
                        </label>
                    </div>
                    <div class="icheck-primary">
                        <input type="checkbox" id="checkboxPrimary3" name="day_schedule[]" value='Tuesday'  <?php echo isset($meta['day_schedule']) && in_array("Tuesday",explode(",",$meta['day_schedule'])) ? "checked" : ''  ?>>
                        <label for="checkboxPrimary3">
                            Tuesday
                        </label>
                    </div>
                    <div class="icheck-primary">
                        <input type="checkbox" id="checkboxPrimary4" name="day_schedule[]" value='Wednesday'  <?php echo isset($meta['day_schedule']) && in_array("Wednesday",explode(",",$meta['day_schedule'])) ? "checked" : ''  ?>>
                        <label for="checkboxPrimary4">
                            Wednesday
                        </label>
                    </div>
                    <div class="icheck-primary">
                        <input type="checkbox" id="checkboxPrimary5" name="day_schedule[]" value='Thursday'  <?php echo isset($meta['day_schedule']) && in_array("Thursday",explode(",",$meta['day_schedule'])) ? "checked" : ''  ?>>
                        <label for="checkboxPrimary5">
                            Thursday
                        </label>
                    </div>
                    <div class="icheck-primary">
                        <input type="checkbox" id="checkboxPrimary6" name="day_schedule[]" value='Friday'  <?php echo isset($meta['day_schedule']) && in_array("Friday",explode(",",$meta['day_schedule'])) ? "checked" : ''  ?>>
                        <label for="checkboxPrimary6">
                            Friday
                        </label>
                    </div>
                    <div class="icheck-primary">
                        <input type="checkbox" id="checkboxPrimary7" name="day_schedule[]" value='Saturday'  <?php echo isset($meta['day_schedule']) && in_array("Saturday",explode(",",$meta['day_schedule'])) ? "checked" : ''  ?>>
                        <label for="checkboxPrimary7">
                            Saturday
                        </label>
                    </div>
                </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="" class="control-label">Morning Time Schedule</label>
                            <div class="row row-cols-3">
                            <input type="time" class="form-control col" name="morning_schedule[]" value="<?php echo isset($meta['morning_schedule']) ? explode(',',$meta['morning_schedule'])[0] : "" ?>" required>
                            <span class="col-auto"> - </span>
                            <input type="time" class="form-control col" name="morning_schedule[]" value="<?php echo isset($meta['morning_schedule']) ? explode(',',$meta['morning_schedule'])[1] : "" ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Afternoon Time Schedule</label>
                        <div class="row row-cols-3">
                            <input type="time" class="form-control col" name="afternoon_schedule[]" value="<?php echo isset($meta['afternoon_schedule']) ? explode(',',$meta['afternoon_schedule'])[0] : "" ?>" required>
                            <span class="col-auto"> - </span>
                            <input type="time" class="form-control col" name="afternoon_schedule[]" value="<?php echo isset($meta['afternoon_schedule']) ? explode(',',$meta['afternoon_schedule'])[1] : "" ?>" required>
                        </div>
                    </div>
                </div>
                </div>
			</form>
		</div>
		<div class="card-footer">
			<div class="col-md-12">
				<div class="row">
					<button class="btn btn-sm btn-primary" form="schedule_settings">Update</button>
				</div>
			</div>
		</div>

	</div>
</div>
<script>
	
	$(function(){
        $('#schedule_settings').submit(function(e){
            e.preventDefault()
            start_loader()
            $.ajax({
                url:_base_url_+"classes/Master.php?f=save_sched_settings",
                data: $(this).serialize(),
                method:"POST",
                dataType:"json",
                error:err=>{
                    console.log(err)
                    alert_toast("An error occured",'error');
                    end_loader()
                },
                success:function(resp){
                    if(!!resp.status && resp.status == 'success'){
                        location.reload()
                    }else if(!!resp.status && resp.status == 'success' && !!resp.msg){
                        var err_el = $('<div>')
                            err_el.addClass('alert alert-danger')
                            err_el.text(resp.msg)
                            $('#msg').hide().append(err_el).show('slow')
                            $("html, body").animate({ scrollTop: 0 }, "fast");
                            
                    }else{
                        console.log(resp)
                        alert_toast("An error occured",'error');
                    }
                    end_loader();
                }
            })
        })
    })
</script>

