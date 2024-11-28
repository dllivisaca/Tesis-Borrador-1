<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/pacientes.css">
    <title>Pacientes</title>
    
</head>
<body>
    <?php
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

    if($_POST){
        $keyword=$_POST["search"];
        
        $sqlmain= "select * from paciente where pacusuario='$keyword' or pacnombre='$keyword' or pacnombre like '$keyword%' or pacnombre like '%$keyword' or pacnombre like '%$keyword%' ";
    }else{
        $sqlmain= "select * from paciente order by pacid desc";

    }

    // Ejecuta la consulta y cuenta los resultados
    $result = $database->query($sqlmain);
    $num_results = $result->num_rows; // Aquí se cuenta el número de doctores encontrados
    ?>
    <div class="container">
    <div class="menu">
            <div class="profile-container">
                <img src="../img/logo.png" alt="Logo" class="menu-logo">
                <p class="profile-title">Administrador</p>
            </div>
            <a href="../logout.php"><button class="btn-logout">Cerrar sesión</button></a>
            <div class="linea-separadora"></div>
            <div class="menu-links">
                <a href="dashboard.php" class="menu-link">Dashboard</a>
                <a href="doctores.php" class="menu-link">Doctores</a>
                <a href="pacientes.php" class="menu-link menu-link-active">Pacientes</a>
                <a href="horarios2.php" class="menu-link">Horarios disponibles</a>
                <a href="citas.php" class="menu-link">Citas agendadas</a>
                <a href="opiniones_recibidas.php" class="menu-link">Opiniones recibidas</a>
            </div>
        </div>
        <div class="dash-body">
            <div class="header-actions">
            <!-- Sección izquierda: Botón Atrás y barra de búsqueda -->
            <div class="header-left">
                <a href="pacientes.php">
                    <button class="btn-action">← Atrás</button>
                </a>
                <form action="" method="post" class="search-bar">
                    <input type="search" name="search" placeholder="Escribe el nombre del paciente" list="pacientes" value="<?php echo isset($_POST['search']) ? htmlspecialchars($_POST['search']) : ''; ?>">
                    <input type="submit" value="Buscar">
                </form>
            </div>
        </div>

        <form action="" method="post" class="header-search">       
            <?php
                echo '<datalist id="pacientes">';
                $list11 = $database->query("select  pacnombre,pacusuario from paciente;");

                for ($y=0;$y<$list11->num_rows;$y++){
                    $row00=$list11->fetch_assoc();
                    $d=$row00["pacnombre"];
                    $c=$row00["pacusuario"];
                    echo "<option value='$d'><br/>";
                    echo "<option value='$c'><br/>";
                };

            echo ' </datalist>';
            ?>                                             
        </form> 
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-right: 15px; height: 50px;">
            <p class="heading-main12" style="margin: 0; font-size: 17px; color: rgb(49, 49, 49); align-self: center;">
                Todos los Pacientes (<?php echo $num_results; ?>)
            </p>
            <a href="#" onclick="openModal()" class="non-style-link">
                <button class="btn-add" style="align-self: center;">+ Agregar nuevo paciente</button>
            </a>
        </div>
                                 
                  
            <tr>
                <td colspan="4">
                    <center>
                    <div class="abc scroll">
                    <table class="sub-table scrolldown" border="0">
                    <thead>
                    <tr>
                            <th class="table-headin">
                                
                            
                            Nombre del Paciente
                            
                            </th>
                            <th class="table-headin">
                            
                        
                            Usuario
                            
                            </th>
                            <th class="table-headin">
                                
                            
                                Teléfono
                                
                            </th>
                            
                            <th class="table-headin">
                                Acciones
                            </th>
                            
                    </thead>
                    <tbody>
                    
                        <?php

                            
                            $result= $database->query($sqlmain);

                            if($result->num_rows==0){
                                echo '<tr>
                                <td colspan="4">
                                <br><br><br><br>
                                <center>
                                
                                
                                <br>
                                <p class="heading-main12" style="margin-left: 45px;font-size:16px;color:rgb(49, 49, 49)">No se encontraron resultados para el nombre ingresado. Intenta con otro término o revisa si está correctamente escrito.</p>
                                </center>
                                <br><br><br><br>
                                </td>
                                </tr>';
                                
                            }
                            else{
                            for ( $x=0; $x<$result->num_rows;$x++){
                                $row=$result->fetch_assoc();
                                $pacid=$row["pacid"];
                                $nombre=$row["pacnombre"];
                                $usuario=$row["pacusuario"];
                                /* $ci=$row["pacci"];
                                $fecnac=$row["pacfecnac"]; */
                                $telf=$row["pactelf"];
                                
                                echo '<tr>
                                    <td>' . substr($nombre, 0, 30) . '</td>
                                    <td>' . substr($usuario, 0, 20) . '</td>
                                    <td>' . substr($telf, 0, 20) . '</td>
                                    <td>
                                        <div style="display:flex;justify-content: center;">
                                        <a href="#" class="non-style-link">
                                            <button 
                                                class="btn-action edit-button" 
                                                data-pacid="' . $pacid . '" 
                                                data-name="' . htmlspecialchars($nombre, ENT_QUOTES) . '" 
                                                data-usuario="' . htmlspecialchars($usuario, ENT_QUOTES) . '" 
                                                data-ci="' . htmlspecialchars($row['pacci'], ENT_QUOTES) . '" 
                                                data-telf="' . htmlspecialchars($row['pactelf'], ENT_QUOTES) . '" 
                                                data-direccion="' . htmlspecialchars($row['pacdireccion'], ENT_QUOTES) . '" 
                                                data-fecnac="' . htmlspecialchars($row['pacfecnac'], ENT_QUOTES) . '" 
                                                style="padding-top: 12px; padding-bottom: 12px; margin-top: 5px;">
                                                <font class="tn-in-text">Editar</font>
                                            </button>
                                        </a>                                    
                                        &nbsp;&nbsp;&nbsp;
                                        <a href="#" class="non-style-link">
                                            <button 
                                                class="btn-action view-button" 
                                                data-pacid="' . $pacid . '" 
                                                data-name="' . htmlspecialchars($nombre, ENT_QUOTES) . '" 
                                                data-usuario="' . htmlspecialchars($usuario, ENT_QUOTES) . '" 
                                                data-ci="' . htmlspecialchars($row['pacci'], ENT_QUOTES) . '" 
                                                data-telf="' . htmlspecialchars($row['pactelf'], ENT_QUOTES) . '" 
                                                data-direccion="' . htmlspecialchars($row['pacdireccion'], ENT_QUOTES) . '" 
                                                data-fecnac="' . htmlspecialchars($row['pacfecnac'], ENT_QUOTES) . '" 
                                                style="padding-top: 12px; padding-bottom: 12px; margin-top: 5px;">
                                                <font class="tn-in-text">Ver más</font>
                                            </button>
                                        </a>
                                    &nbsp;&nbsp;&nbsp;
                                    <a href="#" class="non-style-link">
                                        <button 
                                            class="btn-action delete-button" 
                                            data-pacid="' . $pacid . '" 
                                            data-name="' . htmlspecialchars($nombre, ENT_QUOTES) . '" 
                                            style="padding-top: 12px; padding-bottom: 12px; margin-top: 5px;">
                                            <font class="tn-in-text">Eliminar</font>
                                        </button>
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
        if($action=='drop'){
            $nameget=$_GET["name"];
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2>Are you sure?</h2>
                        <a class="close" href="doctores.php">&times;</a>
                        <div class="content">
                            You want to delete this record<br>('.substr($nameget,0,40).').
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <a href="borrar_paciente.php?id='.$id.'" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"<font class="tn-in-text">&nbsp;Yes&nbsp;</font></button></a>&nbsp;&nbsp;&nbsp;
                        <a href="pacientes.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font></button></a>

                        </div>
                    </center>
            </div>
            </div>
            ';
        }elseif($action=='view'){
            $sqlmain= "select * from paciente where pacid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $nombre=$row["pacnombre"];
            $usuario=$row["pacusuario"];
            $ci=$row["pacci"];
            $fecnac=$row["pacfecnac"];
            $telf=$row["pactelf"];
            $direccion=$row["pacdireccion"];
            
            
            /* $especial_res= $database->query("select espnombre from especialidades where id='$espe'");
            $especial_array= $especial_res->fetch_assoc();
            $especial_name=$especial_array["espnombre"];
            $ci=$row['docci'];
            $telf=$row['doctelf']; */
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2></h2>
                        <a class="close" href="pacientes.php">&times;</a>
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
                                    <label for="name" class="form-label">Nombre: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.$nombre.'<br><br>
                                </td>
                                
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Usuario" class="form-label">Usuario: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$usuario.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="ci" class="form-label">CI: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$ci.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Telf" class="form-label">Teléfono: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$telf.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="espec" class="form-label">Fecha de Nacimiento: </label>
                                    
                                </td>
                            </tr>
                            <tr>
                            <td class="label-td" colspan="2">
                            '.$fecnac.'<br><br>
                            </td>
                            </tr>
                            
                            

                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="espec" class="form-label">Dirección: </label>
                                    
                                </td>
                            </tr>
                            <tr>
                            <td class="label-td" colspan="2">
                            '.$direccion.'<br><br>
                            </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="doctores.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                    
                                </td>
                
                            </tr>
                           

                        </table>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>
            ';

        }elseif($action=='add'){
            $error_1=$_GET["error"];
            $errorlist= array(
                '1'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Already have an account for this Email address.</label>',
                '2'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password Conformation Error! Reconform Password</label>',
                '3'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;"></label>',
                '4'=>"",
                '0'=>'',

            );
            if($error_1!='4'){
            echo '
        <div id="popup1" class="overlay">
                <div class="popup">
                <center>
                
                    <a class="close" href="doctores.php">&times;</a> 
                    <div style="display: flex;justify-content: center;">
                    <div class="abc">
                    <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                    <tr>
                            <td class="label-td" colspan="2">'.
                                $errorlist[$error_1]
                            .'</td>
                        </tr>
                        <tr>
                            <td>
                                <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Agregar nuevo paciente.</p><br><br>
                            </td>
                        </tr>
                        
                        <tr>
                            <form action="agregar_paciente.php" method="POST" class="agregar_paciente-form">
                            <td class="label-td" colspan="2">
                                <label for="name" class="form-label">Nombre: </label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <input type="text" name="name" class="input-text" placeholder="Nombre del paciente" required><br>
                            </td>
                            
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <label for="Usuario" class="form-label">Usuario: </label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <input type="text" name="usuario" class="input-text" placeholder="Usuario del paciente" required><br>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <label for="ci" class="form-label">CI: </label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <input type="text" name="ci" class="input-text" placeholder="Número de cédula" required><br>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <label for="Telf" class="form-label">Teléfono: </label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <input type="tel" name="Telf" class="input-text" placeholder="Número de teléfono" required><br>
                            </td>
                        </tr>

                        <tr>
                            <td class="label-td" colspan="2">
                                <label for="direccion" class="form-label">Dirección: </label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <input type="text" name="direccion" class="input-text" placeholder="Dirección" required><br>
                            </td>
                        </tr>

                        <tr>
                            <td class="label-td" colspan="2">
                                <label for="fecnac" class="form-label">Fecha de nacimiento: </label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <input type="date" name="fecnac" class="input-text" placeholder="Fecha de nacimiento" required><br>
                            </td>
                        </tr>
                        
                        
                        <tr>
                            <td class="label-td" colspan="2">
                                <label for="password" class="form-label">Password: </label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <input type="password" name="password" class="input-text" placeholder="Defind a Password" required><br>
                            </td>
                        </tr><tr>
                            <td class="label-td" colspan="2">
                                <label for="cpassword" class="form-label">Conform Password: </label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <input type="password" name="cpassword" class="input-text" placeholder="Conform Password" required><br>
                            </td>
                        </tr>
                        
            
                        <tr>
                            <td colspan="2">
                                <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            
                                <input type="submit" value="Add" class="login-btn btn-primary btn">
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

        }else{
            echo '
                <div id="popup1" class="overlay">
                        <div class="popup">
                        <center>
                        <br><br><br><br>
                            <h2>New Record Added Successfully!</h2>
                            <a class="close" href="doctores.php">&times;</a>
                            <div class="content">
                                
                                
                            </div>
                            <div style="display: flex;justify-content: center;">
                            
                            <a href="doctores.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>

                            </div>
                            <br><br>
                        </center>
                </div>
                </div>
    ';
        }
    }elseif($action=='edit'){
        $sqlmain= "select * from paciente where pacid='$id'";
        $result= $database->query($sqlmain);
        $row=$result->fetch_assoc();
        $name=$row["pacnombre"];
        $usuario=$row["pacusuario"];
        $direccion=$row["pacdireccion"];
        $ci=$row['pacci'];
        $fecnac=$row['pacfecnac'];  
        $telf=$row['pactelf'];

        $error_1=$_GET["error"];
            $errorlist= array(
                '1'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Already have an account for this Email address.</label>',
                '2'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password Conformation Error! Reconform Password</label>',
                '3'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;"></label>',
                '4'=>"",
                '0'=>'',

            );

        if($error_1!='4'){
                echo '
                <div id="popup1" class="overlay">
                        <div class="popup">
                        <center>
                        
                            <a class="close" href="doctores.php">&times;</a> 
                            <div style="display: flex;justify-content: center;">
                            <div class="abc">
                            <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                            <tr>
                                    <td class="label-td" colspan="2">'.
                                        $errorlist[$error_1]
                                    .'</td>
                                </tr>
                                <tr>
                                    <td>
                                        <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Edita los Detalles del Paciente.</p>
                                    Paciente ID : '.$id.' (Generado automáticamente)<br><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <form action="editar_paciente.php" method="POST" class="agregar_paciente-form">
                                        <label for="Usuario" class="form-label">Usuario: </label>
                                        <input type="hidden" value="'.$id.'" name="id00">
                                        <input type="hidden" name="oldusuario" value="'.$usuario.'" >
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                    <input type="text" name="usuario" class="input-text" placeholder="Nombre de usuario" value="'.$usuario.'" required><br>
                                    </td>
                                </tr>
                                <tr>
                                    
                                    <td class="label-td" colspan="2">
                                        <label for="name" class="form-label">Nombre: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="text" name="name" class="input-text" placeholder="Nombre del paciente" value="'.$name.'" required><br>
                                    </td>
                                    
                                </tr>
                                
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="ci" class="form-label">CI: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="text" name="ci" class="input-text" placeholder="CI Number" value="'.$ci.'" required><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="Telf" class="form-label">Teléfono: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="tel" name="Telf" class="input-text" placeholder="Telephone Number" value="'.$telf.'" required><br>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="direccion" class="form-label">Dirección: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="text" name="direccion" class="input-text" placeholder="Dirección del paciente" value="'.$direccion.'" required><br>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="fecnac" class="form-label">Fecha de nacimiento: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="date" name="fecnac" class="input-text" placeholder="Fecha de nacimiento del paciente" value="'.$fecnac.'" required><br>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="password" class="form-label">Contraseña: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="password" name="password" class="input-text" placeholder="Define una Contraseña" required><br>
                                    </td>
                                </tr><tr>
                                    <td class="label-td" colspan="2">
                                        <label for="cpassword" class="form-label">Confirma la Contraseña: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="password" name="cpassword" class="input-text" placeholder="Confirma la Contraseña" required><br>
                                    </td>
                                </tr>
                                
                    
                                <tr>
                                    <td colspan="2">
                                        <input type="reset" value="Resetear" class="login-btn btn-primary-soft btn" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    
                                        <input type="submit" value="Guardar" class="login-btn btn-primary btn">
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
    }else{
        echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                    <br><br><br><br>
                        <h2>Edit Successfully!</h2>
                        <a class="close" href="doctores.php">&times;</a>
                        <div class="content">
                            
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        
                        <a href="doctores.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>

                        </div>
                        <br><br>
                    </center>
            </div>
            </div>
';



    }; };
};

?>










<!--             /* $sqlmain= "select * from paciente where pacid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $name=$row["pname"];
            $email=$row["pemail"];
            $nic=$row["pnic"];
            $dob=$row["pdob"];
            $tele=$row["ptel"];
            $address=$row["paddress"];
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <a class="close" href="patient.php">&times;</a>
                        <div class="content">

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
                                    <label for="name" class="form-label">Patient ID: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    P-'.$id.'<br><br>
                                </td>
                                
                            </tr>
                            
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Name: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.$name.'<br><br>
                                </td>
                                
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Email" class="form-label">Email: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$email.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="nic" class="form-label">NIC: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$nic.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Tele" class="form-label">Teléfono: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$tele.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="spec" class="form-label">Address: </label>
                                    
                                </td>
                            </tr>
                            <tr>
                            <td class="label-td" colspan="2">
                            '.$address.'<br><br>
                            </td>
                            </tr>
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Date of Birth: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.$dob.'<br><br>
                                </td>
                                
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="patient.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                
                                    
                                </td>
                
                            </tr>
                           

                        </table>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>
            '; */
        
    };

?> -->
</div>

</body>
</html>