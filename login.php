<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/login.css">
        
    <title>Inicio de sesión</title>
</head>
<body>
    <?php

    session_start();

    $_SESSION["usuario"]="";
    $_SESSION["usuario_rol"]="";
    
    date_default_timezone_set('America/Guayaquil');
    $fecha = date('Y-m-d');

    $_SESSION["date"]=$fecha;
    
    //importar base de datos
    include("conexion_db.php");

    if($_POST){

        $usuario=$_POST['usuario'];
        $password=$_POST['usuario_password'];
        
        $error='<label for="promter" class="form-label"></label>';

        $result= $database->query("select * from usuarios where usuario='$usuario'");
        if($result->num_rows==1){
            $usuario_rol=$result->fetch_assoc()['usuario_rol'];
            // Paciente
            if ($usuario_rol=='pac'){
                $checker = $database->query("select * from paciente where pacusuario='$usuario' and pacpassword='$password'");
                if ($checker->num_rows==1){
                    //Vista de paciente
                    $_SESSION['usuario']=$usuario;
                    $_SESSION['usuario_rol']='pac';
                    
                    header('location: inicio_paciente.html');
                }else{
                    $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Error: Usuario o contraseña incorrectos</label>';
                }
            // Administrador    
            }elseif($usuario_rol=='adm'){
                $checker = $database->query("select * from administrador where admusuario ='$usuario' and admpassword='$password'");
                if ($checker->num_rows==1){
                    //Vista de administrador
                    $_SESSION['usuario']=$usuario;
                    $_SESSION['usuario_rol']='adm';
                    
                    header('location: admin/doctores.php');
                    //header('location: inicio_admin.html');
                }else{
                    $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Error: Usuario o contraseña incorrectos</label>';
                }
            // Doctor
            }elseif($usuario_rol=='doc'){
                $checker = $database->query("select * from doctor where docusuario ='$usuario' and docpassword='$password'");
                if ($checker->num_rows==1){
                    //Vista de doctor
                    $_SESSION['usuario']=$usuario;
                    $_SESSION['usuario_rol']='doc';
                    header('location: inicio_doctor.html');
                }else{
                    $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Error: Usuario o contraseña incorrectos</label>';
                }
            }
        }else{
            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">No existe cuenta creada para el usuario ingresado</label>';
        }
    }else{
        $error='<label for="promter" class="form-label">&nbsp;</label>';
    }
    ?>

    <center>
    <div class="container">
        <table border="0" style="margin: 0;padding: 0;width: 60%;">
            <tr>
                <td>
                    <p class="header-text">Bienvenido de vuelta!</p>
                </td>
            </tr>
        <div class="form-body">
            <tr>
                <td>
                    <p class="sub-text">Inicia sesión para continuar</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td">
                    <label for="usuario" class="form-label">Usuario: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <input type="text" name="usuario" class="input-text" placeholder="Ingresa tu usuario" required>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <label for="usuario_password" class="form-label">Contraseña: </label>
                </td>
            </tr>

            <tr>
                <td class="label-td">
                    <input type="Password" name="usuario_password" class="input-text" placeholder="Ingresa tu contraseña" required>
                </td>
            </tr>

            <tr>
                <td><br>
                <?php echo $error ?>
                </td>
            </tr>

            <tr>
                <td>
                    <input type="submit" value="Iniciar sesión" class="login-btn btn-primary btn">
                </td>
            </tr>
        </div>
            <tr>
                <td>
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">¿Deseas agendar tu cita pero no tienes cuenta&#63; </label>
                    <a href="registro.php" class="hover-link1 non-style-link">Crear cuenta ahora</a>
                    <br><br><br>
                </td>
            </tr>
                        
                </form>
        </table>

    </div>
</center>
</body>
</html>