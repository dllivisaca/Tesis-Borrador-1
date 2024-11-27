<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/doctores.css">
    <title>Doctores</title>
    
</head>
<body>
    <?php
    error_reporting(E_ERROR | E_PARSE);

    session_start();

    if (isset($_GET['success']) && $_GET['success'] == 1) {
        echo '<script>
            window.onload = function() {
                document.getElementById("successModal").style.display = "flex";
            };
        </script>';
        // Redirigir para eliminar el parámetro de la URL
        echo '<script>
            const url = new URL(window.location.href);
            url.searchParams.delete("success");
            window.history.replaceState({}, document.title, url.toString());
        </script>';
    }

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
        
        $sqlmain= "select * from doctor where docnombre='$keyword' or docnombre like '$keyword%' or docnombre like '%$keyword' or docnombre like '%$keyword%'";
    }else{
        $sqlmain= "select * from doctor order by docid desc";
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
                <a href="doctores.php" class="menu-link menu-link-active">Doctores</a>
                <a href="pacientes.php" class="menu-link">Pacientes</a>
                <a href="horarios2.php" class="menu-link">Horarios disponibles</a>
                <a href="citas.php" class="menu-link">Citas agendadas</a>
                <a href="opiniones_recibidas.php" class="menu-link">Opiniones recibidas</a>
            </div>
        </div>
        <div class="dash-body">
            <div class="header-actions">
            <!-- Sección izquierda: Botón Atrás y barra de búsqueda -->
            <div class="header-left">
                <a href="doctores.php">
                    <button class="btn-action">← Atrás</button>
                </a>
                <form action="" method="post" class="search-bar">
                    <input type="search" name="search" placeholder="Escribe el nombre del doctor" list="doctores" value="<?php echo isset($_POST['search']) ? htmlspecialchars($_POST['search']) : ''; ?>">
                    <input type="submit" value="Buscar">
                </form>
            </div>
        </div>
                
            <form action="" method="post" class="header-search">
                
                
                <?php
                    echo '<datalist id="doctores">';
                    $list11 = $database->query("select docnombre,docusuario from  doctor;");

                    for ($y=0;$y<$list11->num_rows;$y++){
                        $row00=$list11->fetch_assoc();
                        $d=$row00["docnombre"];
                        $c=$row00["docusuario"];
                        echo "<option value='$d'><br/>";
                        echo "<option value='$c'><br/>";
                    };

                echo ' </datalist>';
                ?>                                             
            </form>
                  
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-right: 15px; height: 50px;">
                    <p class="heading-main12" style="margin: 0; font-size: 17px; color: rgb(49, 49, 49); align-self: center;">
                        Todos los Doctores (<?php echo $num_results; ?>)
                    </p>
                    <a href="#" onclick="openModal()" class="non-style-link">
                        <button class="btn-add" style="align-self: center;">+ Agregar nuevo doctor</button>
                    </a>
                </div>
                
                <?php
                    if($_POST){
                        $keyword=$_POST["search"];
                        
                        $sqlmain= "select * from doctor where docnombre='$keyword' or docnombre like '$keyword%' or docnombre like '%$keyword' or docnombre like '%$keyword%'";
                    }else{
                        $sqlmain= "select * from doctor order by docid desc";
                    }

                    
                ?>
                  
                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table  class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                                <th class="table-headin">
                                    
                                
                                    Nombre del Doctor
                                
                                </th>
                                <th class="table-headin">
                                    Usuario
                                </th>
                                <th class="table-headin">
                                    
                                    Especialidad
                                    
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
                                    $docid=$row["docid"];
                                    $name=$row["docnombre"];
                                    $usuario=$row["docusuario"];
                                    $espe=$row["especialidades"];
                                    $especial_res= $database->query("select espnombre from especialidades where id='$espe'");
                                    $especial_array= $especial_res->fetch_assoc();
                                    $especial_name=$especial_array["espnombre"];
                                    echo '<tr>
                                        <td> &nbsp;'.
                                        substr($name,0,30)
                                        .'</td>
                                        <td>
                                        '.substr($usuario,0,20).'
                                        </td>
                                        <td>
                                            '.substr($especial_name,0,20).'
                                        </td>

                                        <td>
                                        <div style="display:flex;justify-content: center;">
                                        <a href="?action=edit&id='.$docid.'&error=0" class="non-style-link"><button  class="btn-action"  style="padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Editar</font></button></a>
                                        &nbsp;&nbsp;&nbsp;
                                        <a href="?action=view&id='.$docid.'" class="non-style-link"><button  class="btn-action"  style="padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Ver más</font></button></a>
                                       &nbsp;&nbsp;&nbsp;
                                       <a href="?action=drop&id='.$docid.'&name='.$name.'" class="non-style-link"><button  class="btn-action"  style="padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Eliminar</font></button></a>
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
       
            </>
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
                        <a href="borrar_doctor.php?id='.$id.'" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"<font class="tn-in-text">&nbsp;Yes&nbsp;</font></button></a>&nbsp;&nbsp;&nbsp;
                        <a href="doctores.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font></button></a>

                        </div>
                    </center>
            </div>
            </div>
            ';
        }elseif($action=='view'){
            $sqlmain= "select * from doctor where docid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $name=$row["docnombre"];
            $usuario=$row["docusuario"];
            $espe=$row["especialidades"];
            
            $especial_res= $database->query("select espnombre from especialidades where id='$espe'");
            $especial_array= $especial_res->fetch_assoc();
            $especial_name=$especial_array["espnombre"];
            $ci=$row['docci'];
            $telf=$row['doctelf'];
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2></h2>
                        <a class="close" href="doctores.php">&times;</a>
                        <div style="display: flex;justify-content: center;">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                        
                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Ver detalles</p><br><br>
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
                                    <label for="Telf" class="form-label">Telephone: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$telf.'<br><br>
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
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Add New Doctor.</p><br><br>
                                </td>
                            </tr>
                            
                            <tr>
                                <form action="agregar_doctor.php" method="POST" class="agregar_doctor-form">
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Name: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="text" name="name" class="input-text" placeholder="Doctor Name" required><br>
                                </td>
                                
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Usuario" class="form-label">Usuario: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="text" name="usuario" class="input-text" placeholder="Email Address" required><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="ci" class="form-label">CI: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="text" name="ci" class="input-text" placeholder="CI Number" required><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Telf" class="form-label">Telephone: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="tel" name="Telf" class="input-text" placeholder="Telephone Number" required><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="espec" class="form-label">Choose especialidades: </label>
                                    
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <select name="espec" id="" class="box" >';
                                        
        
                                        $list11 = $database->query("select  * from  especialidades order by espnombre asc;");
        
                                        for ($y=0;$y<$list11->num_rows;$y++){
                                            $row00=$list11->fetch_assoc();
                                            $sn=$row00["espnombre"];
                                            $id00=$row00["id"];
                                            echo "<option value=".$id00.">$sn</option><br/>";
                                        };
        
        
        
                                        
                        echo     '       </select><br>
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
            $sqlmain= "select * from doctor where docid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $name=$row["docnombre"];
            $usuario=$row["docusuario"];
            $espe=$row["especialidades"];
            
            $especial_res= $database->query("select espnombre from especialidades where id='$espe'");
            $especial_array= $especial_res->fetch_assoc();
            $especial_name=$especial_array["espnombre"];
            $ci=$row['docci'];
            $telf=$row['doctelf'];

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
                                            <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Edita los Detalles del Doctor.</p>
                                        Doctor ID : '.$id.' (Generado automáticamente)<br><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <form action="editar_doctor.php" method="POST" class="agregar_doctor-form">
                                            <label for="Usuario" class="form-label">Usuario: </label>
                                            <input type="hidden" value="'.$id.'" name="id00">
                                            <input type="hidden" name="oldusuario" value="'.$usuario.'" >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                        <input type="text" name="usuario" class="input-text" placeholder="Email Address" value="'.$usuario.'" required><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        
                                        <td class="label-td" colspan="2">
                                            <label for="name" class="form-label">Nombre: </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <input type="text" name="name" class="input-text" placeholder="Doctor Name" value="'.$name.'" required><br>
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
                                            <label for="espec" class="form-label">Escoge la(s) especialidad(es): (Actual: '.$especial_name.')</label>
                                            
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <select name="espec" id="" class="box">';
                                                
                
                                                $list11 = $database->query("select  * from  especialidades;");
                
                                                for ($y=0;$y<$list11->num_rows;$y++){
                                                    $row00=$list11->fetch_assoc();
                                                    $sn=$row00["espnombre"];
                                                    $id00=$row00["id"];
                                                    echo "<option value=".$id00.">$sn</option><br/>";
                                                };
                
                
                
                                                
                                echo     '       </select><br><br>
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
</div>



<script>
  function openModal() {
    document.getElementById("addDoctorModal").style.display = "flex";
  }

  function closeModal() {
    document.getElementById("addDoctorModal").style.display = "none";
  }
</script>

<div id="addDoctorModal" class="overlay">
  <div class="popup">
    
    <h2>Agregar Nuevo Doctor</h2>
    <form action="agregar_doctor.php" method="POST" class="doctor-form" onsubmit="return validateForm()">
      <div class="form-group">
        <label for="name">Nombre del Doctor:</label>
        <input type="text" name="name" id="name" placeholder="Nombre del doctor" required minlength="4">
      </div>
      <div class="form-group">
        <label for="usuario">Nombre de Usuario:</label>
        <input type="text" name="usuario" id="usuario" placeholder="Nombre de usuario" required minlength="4">
      </div>
      <div class="form-group">
        <label for="ci">CI:</label>
        <input type="text" name="ci" id="ci" placeholder="Número de CI" required minlength="10" maxlength="10" pattern="\d+">
      </div>
      <div class="form-group">
        <label for="Telf">Teléfono:</label>
        <input type="text" name="Telf" id="Telf" placeholder="Número de Teléfono" required minlength="10" maxlength="10" pattern="\d+">
      </div>
      <div class="form-group">
        <label for="espec">Especialidad:</label>
        <select name="espec" id="espec" required>
          <option value="">Selecciona una especialidad</option>
          <?php
          $especialidades = $database->query("SELECT * FROM especialidades");
          while ($row = $especialidades->fetch_assoc()) {
            echo "<option value='{$row['id']}'>{$row['espnombre']}</option>";
          }
          ?>
        </select>
      </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input 
                type="password" 
                name="password" 
                id="password" 
                placeholder="Contraseña" 
                required 
                minlength="8"
                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
                onfocus="showPasswordMessage()" 
                onblur="hidePasswordMessage()">
            <small id="passwordMessage" class="password-rules" style="display: none;">Mínimo 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales.</small>
        </div>
      <div class="form-group">
        <label for="cpassword">Confirmar Contraseña:</label>
        <input type="password" name="cpassword" id="cpassword" placeholder="Confirmar Contraseña" required>
      </div>
      <div class="form-buttons">
        <button type="submit">Guardar</button>
        <button type="button" onclick="closeModal()">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
  function validateForm() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('cpassword').value;

    if (password !== confirmPassword) {
      alert('Las contraseñas no coinciden.');
      return false;
    }
    return true;
  }

  function showPasswordMessage() {
      document.getElementById('passwordMessage').style.display = 'block';
  }

  function hidePasswordMessage() {
      document.getElementById('passwordMessage').style.display = 'none';
  }

  // Mostrar el modal si success=1 está en la URL
  if (window.location.search.includes('success=1')) {
    // Mostrar el modal de éxito
    document.getElementById("successModal").style.display = "flex";

    // Eliminar el parámetro "success" de la URL sin recargar la página
    const url = new URL(window.location.href);
    url.searchParams.delete('success');
    window.history.replaceState({}, document.title, url.toString());
  }

  function closeSuccessModal() {
    document.getElementById("successModal").style.display = "none";
  }

  window.addEventListener('scroll', function () {
    const menu = document.querySelector('.menu');
    if (window.scrollY > 0) {
        menu.classList.add('scroll');
        console.log('Clase scroll agregada');
    } else {
        menu.classList.remove('scroll');
        console.log('Clase scroll eliminada');
    }
});

</script>
<div id="successModal" class="overlay" style="display: none;">
  <div class="popup">
    <a href="#" class="close" onclick="closeSuccessModal()">×</a>
    <h2>¡Doctor agregado con éxito!</h2>
    <div class="content">
      El nuevo doctor ha sido registrado correctamente en el sistema.
    </div>
    <div class="form-buttons">
      <button onclick="closeSuccessModal()">Cerrar</button>
    </div>
  </div>
</div>

</body>
</html>