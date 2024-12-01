<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/registro.css">
        
    <title>Crea tu cuenta</title>
    
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

if($_POST){
    $_SESSION["datos_paciente"]=array(
        'primer_nombre'=>$_POST['primer_nombre'],
        'apellido'=>$_POST['apellido'],
        'direccion'=>$_POST['direccion'],
        'ci'=>$_POST['ci'],
        'fecnac'=>$_POST['fecnac']
    );

    print_r($_SESSION["datos_paciente"]);
    header("location: crear_cuenta.php");
}

?>
    <center>
    <div class="container">
        <table border="0">
            <tr>
                <td colspan="2">
                <div class="container">
                <img src="img/logo1.png" alt="Logo FamySALUD" class="logo">
                <table border="0">
                    <p class="header-text">Empecemos</p>
                    <p class="sub-text">Agrega tu información para crear tu perfil</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td" colspan="2">
                    <label for="nombre" class="form-label">Nombre: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <input type="text" name="primer_nombre" class="input-text" placeholder="Primer Nombre" required>
                </td>
                <td class="label-td">
                    <input type="text" name="apellido" class="input-text" placeholder="Apellido" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="direccion" class="form-label">Dirección: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="text" name="direccion" class="input-text" placeholder="Dirección" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="ci" class="form-label">CI: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="text" name="ci" class="input-text" placeholder="Número de cédula" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="fecnac" class="form-label">Fecha de Nacimiento: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="date" name="fecnac" class="input-text" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                </td>
            </tr>

            <tr>
                <td>
                    <input type="reset" value="Borrar" class="login-btn btn-primary-soft btn" >
                </td>
                <td>
                    <input type="submit" value="Siguiente" class="login-btn btn-primary btn">
                </td>

            </tr>
            <tr>
                <td colspan="2">
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">¿Ya tienes una cuenta&#63; </label>
                    <a href="login.php" class="hover-link1 non-style-link">Inicar sesión</a>
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