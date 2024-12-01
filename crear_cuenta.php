<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/crear_cuenta.css">
        
    <title>Crear cuenta</title>
    
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

            echo "<script>
                    alert('¡Cuenta creada exitosamente!');
                    window.location.href = 'login.php';
                </script>";
            exit;
        } 
        
    }else{
        $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">¡Error de confirmación de contraseña! Por favor, confirma nuevamente la contraseña</label>';
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
                    <p class="header-text">Continuemos</p>
                    <p class="sub-text">Bien, ahora crea tu cuenta de usuario</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td" colspan="2">
                    <div class="form-group-inline">
                        <label for="nuevo_usuario" class="form-label-inline">Usuario: </label>
                        <input type="text" name="nuevo_usuario" class="input-text-inline" placeholder="Crea tu nombre de usuario" required minlength="4">
                    </div>
                    
                </td>
            </tr>
            
            
            <tr>
                <td class="label-td" colspan="2">
                    <div class="form-group-inline">
                        <label for="tele" class="form-label-inline">Número de celular: </label>
                        
                        <input 
                            type="text" 
                            class="input-text-inline"
                            name="telf" 
                             
                            placeholder="+593999999999" 
                            required 
                            pattern="\+593\d{9}" 
                            title="El número debe estar en el formato +593999999999">
                    </div>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <div class="form-group-inline">
                        <label for="nueva_password" class="form-label-inline">Crea una contraseña:</label>
                        
                        <input 
                            type="password" 
                            class="input-text-inline"
                            name="nueva_password" 
                            id="password" 
                            placeholder="Crea una contraseña" 
                            required 
                            minlength="8"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
                            onfocus="showPasswordMessage()" 
                            onblur="hidePasswordMessage()">
                        
                    </div>
                    <small id="passwordMessage" class="password-rules" style="display: none;">Mínimo 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales.</small>
                </td>
            </tr>
            
            <tr>
                <td class="label-td" colspan="2">
                    <div class="form-group-inline">
                        <label for="confirmar_password" class="form-label-inline">Confirma la contraseña: </label>
                        <input type="password" name="confirmar_password" class="input-text-inline" placeholder="Confirma la contraseña" required>
                    </div>
                    
                </td>
            </tr>
            
     
            <tr>
                
                <td colspan="2">
                    <?php echo $error ?>

                </td>
            </tr>
            
            <tr>
                <td>
                    <input type="reset" value="Borrar" class="login-btn btn-primary-soft btn" >
                </td>
                <td>
                    <input type="submit" value="Registrarme" class="login-btn btn-primary btn">
                </td>

            </tr>
            <tr>
                <td colspan="2">
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">¿Ya tienes una cuenta&#63; </label>
                    <a href="login.php" class="hover-link1 non-style-link">Inicia sesión</a>
                    <br><br><br>
                </td>
            </tr>

                    </form>
            </tr>
        </table>

    </div>
</center>
    <script>
        function showPasswordMessage() {
            document.getElementById('passwordMessage').style.display = 'block';
        }

        function hidePasswordMessage() {
            document.getElementById('passwordMessage').style.display = 'none';
        }

    </script>
</body>
</html>