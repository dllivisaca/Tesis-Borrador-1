<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Doctor</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
    </style>
</head>
<body>
    <?php
    session_start();

    if (isset($_SESSION["usuario"])) {
        if (($_SESSION["usuario"]) == "" || $_SESSION['usuario_rol'] != 'adm') {
            header("location: ../login.php");
        }
    } else {
        header("location: ../login.php");
    }
    
    // Import database
    include("../conexion_db.php");

    if ($_POST) {
        $result = $database->query("select * from usuarios");

        // Recoger los datos del formulario
        $name = $_POST['name'];
        $ci = $_POST['ci'];
        $espec = $_POST['espec'];
        $usuario = $_POST['usuario'];
        $telf = $_POST['Telf'];
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];

        if ($password == $cpassword) {
            $error = '3';
            
            // Comprobar si el usuario ya existe
            $result = $database->query("select * from usuarios where usuario='$usuario';");
            if ($result->num_rows == 1) {
                $error = '1';
            } else {
                // Hash de la contraseña antes de guardarla
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // Insertar el doctor y el usuario en las respectivas tablas
                $sql1 = "insert into doctor(docusuario,docnombre,docpassword,docci,doctelf,especialidades) 
                         values('$usuario','$name','$hashedPassword','$ci','$telf',$espec);";
                $sql2 = "insert into usuarios values('$usuario','doc','$ci')";

                $database->query($sql1);
                $database->query($sql2);

                $error = '4';
            }
        } else {
            $error = '2'; // Error de confirmación de contraseña
        }
    } else {
        $error = '3'; // Error por falta de datos POST
    }

    // Redirección basada en el resultado
    if ($error == '4') {
        header("location: doctores.php?success=1");
    } else {
        header("location: doctores.php?action=add&error=" . $error);
    }
    ?>
</body>
</html>
