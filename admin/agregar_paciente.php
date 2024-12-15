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
            $error = '1'; // El usuario ya existe
        } else {
            // Hashear la contraseña antes de guardarla
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

             // Primero insertar en la tabla 'usuarios'
             $sql1 = "INSERT INTO usuarios (usuario, usuario_rol, ci) 
             VALUES ('$usuario', 'pac', '$ci')";
            $database->query($sql1);

            // Verificar si hubo un error al insertar en 'usuarios'
            if ($database->error) {
                echo "Error al insertar en 'usuarios': " . $database->error;
                exit();
            }

            // Luego insertar en la tabla 'paciente'
            $sql2 = "INSERT INTO paciente (pacusuario, pacnombre, pacpassword, pacci, pactelf, pacdireccion, pacfecnac) 
                     VALUES ('$usuario', '$name', '$hashedPassword', '$ci', '$telf', '$direccion', '$fecnac')";
            $database->query($sql2);

            // Verificar si hubo un error al insertar en 'paciente'
            if ($database->error) {
                echo "Error al insertar en 'paciente': " . $database->error;
                exit();
            }

            $error = '4'; // Éxito
        }
    } else {
        $error = '2'; // Error de confirmación de contraseña
    }
} else {
    $error = '3'; // Error por falta de datos POST
}

// Redirección basada en el resultado
if ($error == '4') {
    header("location: pacientes.php?success=1");
    exit();
} else {
    header("location: pacientes.php?action=add&error=" . $error);
    exit();
}
?>
