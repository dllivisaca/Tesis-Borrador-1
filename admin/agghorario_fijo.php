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
        ?>
</body>
</html>