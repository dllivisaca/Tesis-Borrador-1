<?php
// Importa la conexión a la base de datos
include("../conexion_db.php");

if ($_POST) {
    // Obtén los datos del formulario
    $name = $_POST['name'];
    $ci = $_POST['ci'];
    $oldusuario = $_POST["oldusuario"];
    $usuario = $_POST['usuario'];
    $telf = $_POST['Telf'];
    $direccion = $_POST['direccion'];
    $fecnac = $_POST['fecnac'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $id = $_POST['id00'];

    // Validar que oldusuario no esté vacío
    if (empty($oldusuario)) {
        die("Error: El valor de 'oldusuario' no se recibió correctamente.");
    }

    // Obtener los valores actuales del paciente desde la base de datos
    $current_data_query = "SELECT * FROM paciente WHERE pacid = $id";
    $current_data_result = $database->query($current_data_query);
    $current_data = $current_data_result->fetch_assoc();

    // Comparar los valores actuales con los nuevos
    $changes = [];
    if ($current_data['pacnombre'] !== $name) $changes[] = "pacnombre='$name'";
    if ($current_data['pacci'] !== $ci) $changes[] = "pacci='$ci'";
    if ($current_data['pactelf'] !== $telf) $changes[] = "pactelf='$telf'";
    if ($current_data['pacdireccion'] !== $direccion) $changes[] = "pacdireccion='$direccion'";
    if ($current_data['pacfecnac'] !== $fecnac) $changes[] = "pacfecnac='$fecnac'";
    if ($current_data['pacusuario'] !== $usuario) $changes[] = "pacusuario='$usuario'";

    // Verificar si se actualizará la contraseña
    if (!empty($password) && !empty($cpassword)) {
        if ($password !== $cpassword) {
            die("Error: Las contraseñas no coinciden.");
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $changes[] = "pacpassword='$hashed_password'";
        }
    }

    // **Verificar si no se hicieron cambios**
    if (empty($changes)) {
        header("location: pacientes.php?edit_success=0"); // Redirige con mensaje de no cambios
        exit();
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

        // Actualizar la tabla 'paciente' si hay cambios
        if (!empty($changes)) {
            $changes[] = "pacusuario='$usuario'"; // Actualiza también el usuario en la tabla paciente
            $sql1 = "UPDATE paciente SET " . implode(", ", $changes) . " WHERE pacid = $id";

            if (!$database->query($sql1)) {
                throw new Exception("Error al actualizar 'paciente': " . $database->error);
            }
        }

        // Confirmar la transacción si todo salió bien
        $database->commit();
        header("Location: pacientes.php?edit_success=1");
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
