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

    // Validar que oldusuario no esté vacío
    if (empty($oldusuario)) {
        die("Error: El valor de 'oldusuario' no se recibió correctamente.");
    }

    // Obtener los valores actuales del doctor desde la base de datos
    $current_data_query = "SELECT * FROM doctor WHERE docid = $id";
    $current_data_result = $database->query($current_data_query);
    $current_data = $current_data_result->fetch_assoc();

    // Compara los valores actuales con los nuevos
    $changes = [];
    if ($current_data['docnombre'] !== $name) $changes[] = "docnombre='$name'";
    if ($current_data['docci'] !== $ci) $changes[] = "docci='$ci'";
    if ($current_data['doctelf'] !== $telf) $changes[] = "doctelf='$telf'";
    if ($current_data['especialidades'] != $espec) $changes[] = "especialidades='$espec'";

    // Asegura que se detecten cambios en el usuario
    if ($current_data['docusuario'] !== $usuario) $changes[] = "docusuario='$usuario'";

    // Verifica si se actualizará la contraseña
    if (!empty($password) && !empty($cpassword)) {
        if ($password !== $cpassword) {
            die("Error: Las contraseñas no coinciden.");
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $changes[] = "docpassword='$hashed_password'";
        }
    }

    // Inicia una transacción para asegurar la consistencia de los datos
    $database->begin_transaction();

    try {
        // Actualiza la tabla 'usuarios' si el nombre de usuario ha cambiado
        if ($oldusuario !== $usuario) {
            // Verificar si el nuevo usuario ya existe
            $checkUserQuery = "SELECT usuario FROM usuarios WHERE usuario='$usuario'";
            $checkUserResult = $database->query($checkUserQuery);

            if ($checkUserResult->num_rows > 0) {
                throw new Exception("Error: El nombre de usuario '$usuario' ya existe en la tabla 'usuarios'.");
            }

            // Actualizar el nombre de usuario en 'usuarios'
            $sql2 = "UPDATE usuarios SET usuario = '$usuario' WHERE usuario = '$oldusuario'";
            

            if (!$database->query($sql2)) {
                throw new Exception("Error al actualizar 'usuarios': " . $database->error);
            }
        }

        // Actualizar la tabla 'doctor' si hay cambios
        if (!empty($changes)) {
            $changes[] = "docusuario='$usuario'"; // Actualiza también el usuario en la tabla doctor
            $sql1 = "UPDATE doctor SET " . implode(", ", $changes) . " WHERE docid = $id";
           

            if (!$database->query($sql1)) {
                throw new Exception("Error al actualizar 'doctor': " . $database->error);
            }
        } else {
            echo "No se ejecutó la actualización porque no hubo cambios. <br>";
        }

        // Confirmar la transacción si todo salió bien
        $database->commit();
        header("Location: doctores.php?edit_success=1");
        exit();
    } catch (Exception $e) {
        // Revertir los cambios si ocurre un error
        $database->rollback();
        die("Error en la transacción: " . $e->getMessage());
    }
} else {
    die("Error: No se recibieron datos POST.");
}
?>
