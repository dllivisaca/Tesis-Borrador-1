<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("../conexion_db.php");

// Verificar autenticación
if (!isset($_SESSION["usuario"]) || $_SESSION["usuario_rol"] != 'adm') {
    header("location: ../login.php");
    exit();
}

// Recoger datos del formulario
$id = $_POST['id00'];
$name = $_POST['name'];
$usuario = $_POST['usuario'];
$ci = $_POST['ci'];
$telf = $_POST['Telf'];
$direccion = $_POST['direccion'];
$fecnac = $_POST['fecnac'];
$password = $_POST['password'];
$cpassword = $_POST['cpassword'];

// Consulta para obtener datos actuales
$current_data_query = $database->query("SELECT * FROM paciente WHERE pacid = '$id'");
$current_data = $current_data_query->fetch_assoc();

// Verificar cambios
$changes = [];
if ($name !== $current_data['pacnombre']) $changes[] = "pacnombre = '$name'";
if ($usuario !== $current_data['pacusuario']) $changes[] = "pacusuario = '$usuario'";
if ($ci !== $current_data['pacci']) $changes[] = "pacci = '$ci'";
if ($telf !== $current_data['pactelf']) $changes[] = "pactelf = '$telf'";
if ($direccion !== $current_data['pacdireccion']) $changes[] = "pacdireccion = '$direccion'";
if ($fecnac !== $current_data['pacfecnac']) $changes[] = "pacfecnac = '$fecnac'";

// Manejar contraseña si se ingresó una nueva
if (!empty($password) && $password === $cpassword) {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $changes[] = "pacpassword = '$hashedPassword'";
} elseif (!empty($password) && $password !== $cpassword) {
    header("location: pacientes.php?action=edit&id=$id&error=2"); // Contraseñas no coinciden
    exit();
}

// Ejecutar actualización si hay cambios
if (count($changes) > 0) {
    $update_query = "UPDATE paciente SET " . implode(', ', $changes) . " WHERE pacid = '$id'";
    $database->query($update_query);

    if ($database->error) {
        echo "Error en la consulta: " . $database->error;
        exit();
    }
    header("location: pacientes.php?edit_success=1"); // Redirige indicando éxito
} else {
    header("location: pacientes.php?edit_success=0"); // Redirige indicando que no hubo cambios
}
exit();
?>
