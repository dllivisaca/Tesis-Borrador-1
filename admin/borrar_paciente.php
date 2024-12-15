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
        header("Location: pacientes.php?error=invalid_id");
        exit();
    }

    // Verificar si el paciente existe
    $result001 = $database->query("SELECT pacusuario FROM paciente WHERE pacid = $id");
    if ($result001 && $result001->num_rows > 0) {
        $usuario = $result001->fetch_assoc()["pacusuario"];
    } else {
        header("Location: pacientes.php?error=patient_not_found");
        exit();
    }

    // Inicializar mensajes de error
    $errorMessages = [];

    // Verificar si el paciente tiene citas agendadas
    $checkCitas = $database->query("SELECT COUNT(*) AS total FROM citas WHERE pacid = $id");
    $citas = $checkCitas->fetch_assoc();
    if ($citas['total'] > 0) {
        $errorMessages[] = "tiene citas agendadas";
    }

    // Verificar si el paciente tiene registros en respuestas_encuestas
    $checkRespuestas = $database->query("SELECT COUNT(*) AS total FROM respuestas_encuestas WHERE pacid = $id");
    $respuestas = $checkRespuestas->fetch_assoc();
    if ($respuestas['total'] > 0) {
        $errorMessages[] = "tiene respuestas en encuestas";
    }

    // Si hay errores, concatenarlos y redirigir con el mensaje completo
    if (!empty($errorMessages)) {
        $errorMessage = "No se puede borrar el paciente porque " . implode(" y ", $errorMessages) . ".";
        header("Location: pacientes.php?error=custom&message=" . urlencode($errorMessage));
        exit();
    }

    // Eliminar usuario relacionado
    $deleteUsuario = $database->query("DELETE FROM usuarios WHERE usuario = '$usuario'");
    if (!$deleteUsuario) {
        header("Location: pacientes.php?delete_success=0");
        exit();
    }

    // Eliminar paciente
    $deletePaciente = $database->query("DELETE FROM paciente WHERE pacid = $id");
    if ($deletePaciente) {
        header("Location: pacientes.php?delete_success=1");
        exit();
    } else {
        header("Location: pacientes.php?delete_success=0");
        exit();
    }
}

header("Location: pacientes.php?delete_success=0");
exit();

?>
