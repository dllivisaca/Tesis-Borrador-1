    <?php

    //learn from w3schools.com
    //Unset all the server side variables

    session_start();

    $_SESSION["usuario"]="";
    $_SESSION["rol"]="";
    
    // Set the new timezone
    date_default_timezone_set('America/Guayaquil');
    $date = date('Y-m-d');

    $_SESSION["fecha"]=$fecha;    

    //import database
    include("conexion_db.php");

    if($_POST){

        $usuario=$_POST['usuario'];
        $password=$_POST['password'];
        
        $error='<label for="promter" class="form-label"></label>';

        $result= $database->query("select * from usuarios where usuario='$usuario'");
        if($result->num_rows==1){
            $rol=$result->fetch_assoc()['rol'];
            if ($rol=='pac'){
                $checker = $database->query("select * from paciente where pacusuario='$usuario' and pacpassword='$password'");
                if ($checker->num_rows==1){

                    //   Patient dashbord
                    $_SESSION['usuario']=$usuario;
                    $_SESSION['rol']='pac';
                    
                    header('location: inicio_paciente.html');

                }else{
                    $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }

            }elseif($rol=='adm'){
                $checker = $database->query("select * from administrador where admusuario ='$usuario' and admpassword='$password'");
                if ($checker->num_rows==1){

                    //   Admin dashbord
                    $_SESSION['usuario']=$usuario;
                    $_SESSION['rol']='adm';
                    
                    header('location: inicio_admin.html');

                }else{
                    $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }

            }elseif($rol=='doc'){
                $checker = $database->query("select * from doctor where docusuario='$usuario' and docpassword='$password'");
                if ($checker->num_rows==1){

                    //   doctor dashbord
                    $_SESSION['usuario']=$usuario;
                    $_SESSION['rol']='doc';
                    header('location: inicio_doctor.html');

                }else{
                    $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }

            }
            
        }else{
            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">We cant found any acount for this email.</label>';
        }

        
    }else{
        $error='<label for="promter" class="form-label">&nbsp;</label>';
    }

    ?>

 