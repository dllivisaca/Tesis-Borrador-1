<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/signup.css">
        
    <title>Crear cuenta</title>
    <style>
        .container{
            animation: transitionIn-X 0.5s;
        }
    </style>
</head>
<body>
<?php

//learn from w3schools.com
//Unset all the server side variables

session_start();

$_SESSION["usuario"]="";
$_SESSION["usuario_rol"]="";

// Set the new timezone
date_default_timezone_set('America/Guayaquil');
$fecha = date('Y-m-d');

$_SESSION["date"]=$fecha;

//importar base de datos
include("conexion_db.php");

if($_POST){

    $result= $database->query("select * from usuarios");

    $primer_nombre=$_SESSION['datos_paciente']['primer_nombre'];
    $apellido=$_SESSION['datos_paciente']['apellido'];
    $nombre=$primer_nombre." ".$apellido;
    $direccion=$_SESSION['datos_paciente']['direccion'];
    $ci=$_SESSION['datos_paciente']['ci'];
    $fecnac=$_SESSION['datos_paciente']['fecnac'];
    $usuario=$_POST['nuevo_usuario'];
    $telf=$_POST['telf'];
    $nueva_password=$_POST['nueva_password'];
    $confirmar_password=$_POST['confirmar_password'];
    
    if ($nueva_password==$confirmar_password){
        $result= $database->query("select * from usuarios where ci='$ci';");
        if($result->num_rows==1){
            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Ya existe una cuenta con este CI.</label>';
        }else{
            $database->query("insert into paciente(pacusuario,pacnombre,pacpassword,pacdireccion,pacci,pacfecnac,pactelf) values('$usuario','$nombre','$nueva_password','$direccion','$ci','$fecnac','$telf');");
            $database->query("insert into usuarios values('$usuario','pac','$ci')");

            //print_r("insert into patient values($pid,'$email','$fname','$lname','$newpassword','$address','$nic','$dob','$tele');");

            $_SESSION["usuario"]=$usuario;
            $_SESSION["usuario_rol"]="pac";
            $_SESSION["nombre_usuario"]=$primer_nombre;

            header('Location: inicio_paciente.html');
            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;"></label>';
        }
        
    }else{
        $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password Confirmation Error! Reconfirm Password</label>';
    }    
}else{
    //header('location: signup.php');
    $error='<label for="promter" class="form-label"></label>';
}

?>
    <center>
    <div class="container">
        <table border="0" style="width: 69%;">
            <tr>
                <td colspan="2">
                    <p class="header-text">Let's Get Started</p>
                    <p class="sub-text">It's Okey, Now Create User Account.</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td" colspan="2">
                    <label for="nuevo_usuario" class="form-label">Usuario: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="text" name="nuevo_usuario" class="input-text" placeholder="Crea tu nombre de usuario" required>
                </td>
                
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="tele" class="form-label">Número de celular: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="tel" name="telf" class="input-text"  placeholder="ex: 0999999999" pattern="[0]{1}[0-9]{9}" >
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="nueva_password" class="form-label">Crea una contraseña:</label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="password" name="nueva_password" class="input-text" placeholder="Crea una contraseña" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="confirmar_password" class="form-label">Confirma la contraseña: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="password" name="confirmar_password" class="input-text" placeholder="Confirma la contraseña" required>
                </td>
            </tr>
     
            <tr>
                
                <td colspan="2">
                    <?php echo $error ?>

                </td>
            </tr>
            
            <tr>
                <td>
                    <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >
                </td>
                <td>
                    <input type="submit" value="Sign Up" class="login-btn btn-primary btn">
                </td>

            </tr>
            <tr>
                <td colspan="2">
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Already have an account&#63; </label>
                    <a href="login.php" class="hover-link1 non-style-link">Login</a>
                    <br><br><br>
                </td>
            </tr>

                    </form>
            </tr>
        </table>

    </div>
</center>
</body>
</html>