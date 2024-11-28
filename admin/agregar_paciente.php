<?php
session_start();

if (isset($_SESSION["usuario"])) {
    if ($_SESSION["usuario"] == "" || $_SESSION['usuario_rol'] != 'adm') {
        header("location: ../login.php");
        exit();
    }
} else {
    header("location: ../login.php");
    exit();
}

// Importar la base de datos
include("../conexion_db.php");

if ($_POST) {
    $name = $_POST['name'];
    $ci = $_POST['ci'];
    $direccion = $_POST['direccion'];
    $usuario = $_POST['usuario'];
    $telf = $_POST['Telf'];
    $fecnac = $_POST['fecnac'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    if ($password == $cpassword) {
        $result = $database->query("SELECT * FROM usuarios WHERE usuario='$usuario';");

        if ($result->num_rows == 1) {
            // Error: usuario ya existe
            header("location: pacientes.php?action=add&error=1");
            exit();
        } else {
            // Inserción en las tablas correspondientes
            $sql1 = "INSERT INTO paciente(pacusuario, pacnombre, pacpassword, pacci, pactelf, pacdireccion, pacfecnac) 
                     VALUES('$usuario', '$name', '$password', '$ci', '$telf', '$direccion', '$fecnac');";
            $sql2 = "INSERT INTO usuarios VALUES('$usuario', 'pac', '$ci');";
            $database->query($sql1);
            $database->query($sql2);

            // Redirigir con éxito
            header("location: pacientes.php?success=1");
            exit();
        }
    } else {
        // Error: contraseñas no coinciden
        header("location: pacientes.php?action=add&error=2");
        exit();
    }
} else {
    // Error: falta de datos
    header("location: pacientes.php?action=add&error=3");
    exit();
}
?>
