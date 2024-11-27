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

    // Verifica si el usuario ya existe
    $result = $database->query("SELECT doctor.docid FROM doctor INNER JOIN usuarios ON doctor.docusuario = usuarios.usuario WHERE usuarios.usuario = '$usuario'");
    if ($result->num_rows == 1) {
        $id2 = $result->fetch_assoc()["docid"];
    } else {
        $id2 = $id;
    }

    // Si el usuario ya existe y no es el actual, genera error
    if ($id2 != $id) {
        $error = '1';
    } else {
        // Si los campos de contraseña están vacíos, no actualices la contraseña
        if (empty($password) && empty($cpassword)) {
            $sql1 = "UPDATE doctor 
                     SET docusuario = '$usuario', 
                         docnombre = '$name', 
                         docci = '$ci', 
                         doctelf = '$telf', 
                         especialidades = $espec 
                     WHERE docid = $id";
        } else {
            // Si las contraseñas no coinciden, genera error
            if ($password !== $cpassword) {
                $error = '2';
            } else {
                // Hashea la nueva contraseña antes de guardarla
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql1 = "UPDATE doctor 
                         SET docusuario = '$usuario', 
                             docnombre = '$name', 
                             docpassword = '$hashed_password', 
                             docci = '$ci', 
                             doctelf = '$telf', 
                             especialidades = $espec 
                         WHERE docid = $id";
            }
        }

        // Si no hay errores, ejecuta la actualización
        if ($error === '3') {
            $database->query($sql1);
            $sql2 = "UPDATE usuarios SET usuario = '$usuario' WHERE usuario = '$oldusuario'";
            $database->query($sql2);
            $error = '4'; // Actualización exitosa
        }
    }
} else {
    // Si no hay datos POST, genera un error genérico
    $error = '3';
}

// Redirige con el código de error correspondiente
header("location: doctores.php?action=edit&error=" . $error . "&id=" . $id);
?>
