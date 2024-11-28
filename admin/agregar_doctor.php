<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (isset($_SESSION["usuario"])) {
    if (($_SESSION["usuario"]) == "" || $_SESSION['usuario_rol'] != 'adm') {
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
    $espec = $_POST['espec'];
    $usuario = $_POST['usuario'];
    $telf = $_POST['Telf'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    if ($password == $cpassword) {
        // Comprobar si el usuario ya existe
        $result = $database->query("SELECT * FROM usuarios WHERE usuario='$usuario';");
        if ($result->num_rows == 1) {
            $error = '1'; // Usuario ya existe
        } else {
            // Hash de la contraseña antes de guardarla
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insertar el doctor y el usuario en las respectivas tablas
            $sql1 = "INSERT INTO doctor (docusuario, docnombre, docpassword, docci, doctelf, especialidades) 
                     VALUES ('$usuario', '$name', '$hashedPassword', '$ci', '$telf', $espec);";
            $sql2 = "INSERT INTO usuarios VALUES ('$usuario', 'doc', '$ci')";

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
    header("location: doctores.php?success=1");
    exit();
} else {
    header("location: doctores.php?action=add&error=" . $error);
    exit();
}
?>
