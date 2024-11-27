<?php
// Importa la conexión a la base de datos
include("../conexion_db.php");

if ($_POST) {
    // Obtén los datos del formulario
    $name = $_POST['name'];
    $ci = $_POST['ci'];
    $oldusuario = $_POST["oldusuario"];
    $espec = $_POST['espec'];
    $usuario = $_POST['usuario'];
    $telf = $_POST['Telf'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $id = $_POST['id00'];

    $error = '3'; // Por defecto, sin errores

    // Obtén los valores actuales del doctor desde la base de datos
    $current_data_query = "SELECT * FROM doctor WHERE docid = $id";
    $current_data_result = $database->query($current_data_query);
    $current_data = $current_data_result->fetch_assoc();

    // Compara los valores actuales con los nuevos
    $changes = [];
    if ($current_data['docnombre'] !== $name) $changes[] = "docnombre='$name'";
    if ($current_data['docci'] !== $ci) $changes[] = "docci='$ci'";
    if ($current_data['docusuario'] !== $usuario) $changes[] = "docusuario='$usuario'";
    if ($current_data['doctelf'] !== $telf) $changes[] = "doctelf='$telf'";
    if ($current_data['especialidades'] != $espec) $changes[] = "especialidades=$espec";

    // Verifica si se actualizará la contraseña
    if (!empty($password) && !empty($cpassword)) {
        if ($password !== $cpassword) {
            $error = '2'; // Contraseñas no coinciden
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $changes[] = "docpassword='$hashed_password'";
        }
    }

    if (empty($changes)) {
        // Si no hay cambios, no se realiza ninguna actualización
        $error = '5'; // Sin cambios
    } else if ($error === '3') {
        // Realiza la actualización si no hay errores
        $sql1 = "UPDATE doctor SET " . implode(", ", $changes) . " WHERE docid = $id";
        $database->query($sql1);

        // Actualiza el usuario solo si el nombre de usuario cambió
        if ($oldusuario !== $usuario) {
            $sql2 = "UPDATE usuarios SET usuario = '$usuario' WHERE usuario = '$oldusuario'";
            $database->query($sql2);
        }
        $error = '4'; // Actualización exitosa
    }
} else {
    $error = '3'; // Error genérico
}

// Redirige según el resultado
if ($error === '4') {
    header("location: doctores.php?edit_success=1");
} elseif ($error === '5') {
    header("location: doctores.php?edit_success=0"); // No se realizaron cambios
} else {
    header("location: doctores.php?action=edit&error=" . $error . "&id=" . $id);
}
?>
