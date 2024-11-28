<?php
session_start();

// Verificar si el usuario tiene permisos
if (isset($_SESSION["usuario"])) {
    if ($_SESSION["usuario"] == "" || $_SESSION["usuario_rol"] != 'adm') {
        header("Location: ../login.php");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}

// Procesar eliminación
if ($_GET) {
    include("../conexion_db.php");

    // Verificar si el ID está presente y es numérico
    if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
        $id = intval($_GET["id"]); // Convertir el ID a entero
    } else {
        // Redirigir si el ID no es válido
        header("Location: pacientes.php?error=invalid_id");
        exit();
    }

    // Verificar si el paciente existe en la base de datos
    $result001 = $database->query("SELECT pacusuario FROM paciente WHERE pacid = $id");

    if ($result001 && $result001->num_rows > 0) {
        $usuario = $result001->fetch_assoc()["pacusuario"]; // Obtener el usuario relacionado
    } else {
        // Si no se encuentra el paciente, redirige con un mensaje de error
        header("Location: pacientes.php?error=patient_not_found");
        exit();
    }

    // Eliminar el usuario relacionado
    $deleteUsuario = $database->query("DELETE FROM usuarios WHERE usuario = '$usuario'");

    if (!$deleteUsuario) {
        // Si no se puede eliminar el usuario, redirige con un mensaje de error
        header("Location: pacientes.php?delete_success=0");
        exit();
    }

    // Eliminar el paciente de la tabla paciente
    $deletePaciente = $database->query("DELETE FROM paciente WHERE pacid = $id");

    if ($deletePaciente) {
        // Redirigir con un mensaje de éxito
        header("Location: pacientes.php?delete_success=1");
        exit();
    } else {
        // Si ocurre un error, redirige con un mensaje de error
        header("Location: pacientes.php?delete_success=0");
        exit();
    }
}

// Redirigir si ocurre un error no controlado
header("Location: pacientes.php?delete_success=0");
exit();
?>
