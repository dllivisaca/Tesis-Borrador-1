<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Validar que el usuario esté autenticado y tenga permisos de administrador
if (isset($_SESSION["usuario"])) {
    if ($_SESSION["usuario"] == "" || $_SESSION['usuario_rol'] != 'adm') {
        header("location: ../login.php");
        exit();
    }
} else {
    header("location: ../login.php");
    exit();
}

// Importar la conexión a la base de datos
include("../conexion_db.php");

if ($_POST) {
    // Recoger los datos del formulario
    $name = $_POST['name'];
    $ci = $_POST['ci'];
    $direccion = $_POST['direccion'];
    $usuario = $_POST['usuario'];
    $telf = $_POST['Telf'];
    $fecnac = $_POST['fecnac'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Verificar si las contraseñas coinciden
    if ($password == $cpassword) {
        // Comprobar si el usuario ya existe
        $result = $database->query("SELECT * FROM usuarios WHERE usuario='$usuario';");
        if ($result->num_rows == 1) {
            // Error: el usuario ya existe
            header("location: pacientes.php?action=add&error=1");
            exit();
        } else {
            // Hashear la contraseña antes de guardarla
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insertar los datos en las tablas correspondientes
            $sql1 = "INSERT INTO paciente (pacusuario, pacnombre, pacpassword, pacci, pactelf, pacdireccion, pacfecnac) 
                     VALUES ('$usuario', '$name', '$hashedPassword', '$ci', '$telf', '$direccion', '$fecnac');";
            $sql2 = "INSERT INTO usuarios (usuario, usuario_rol, ci) VALUES ('$usuario', 'pac', '$ci');";


            // Ejecutar las consultas
            $database->query($sql1);
            if ($database->error) {
                echo "Error en la consulta: " . $database->error;
                exit();
            }
            $database->query($sql2);
            if ($database->error) {
                echo "Error en la consulta: " . $database->error;
                exit();
            }

            // Redirigir con éxito
            header("location: pacientes.php?success=1");
            exit();
        }
    } else {
        // Error: las contraseñas no coinciden
        header("location: pacientes.php?action=add&error=2");
        exit();
    }
} else {
    // Error: falta de datos
    header("location: pacientes.php?action=add&error=3");
    exit();
}
?>
