<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION["usuario"]) || $_SESSION["usuario_rol"] != 'adm') {
    header("Location: ../login.php");
    exit();
}

// Importar la base de datos
include("../conexion_db.php");

if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id = intval($_GET["id"]); // Asegura que el ID sea un número válido

    try {
        // **Verificar si el doctor tiene citas agendadas**
        $checkCitas = $database->query("SELECT COUNT(*) as total FROM citas WHERE docid = $id");
        if (!$checkCitas) {
            throw new Exception("Error al verificar las citas: " . $database->error);
        }

        $citas = $checkCitas->fetch_assoc();
        if ($citas['total'] > 0) {
            // Si el doctor tiene citas, redirige con error
            header("Location: doctores.php?error=doctor_has_appointments");
            exit();
        }

        // Consultar el usuario relacionado con el doctor
        $result001 = $database->query("SELECT docusuario FROM doctor WHERE docid = $id");
        if ($result001 && $result001->num_rows > 0) {
            $usuario = $result001->fetch_assoc()["docusuario"];

            // Eliminar el usuario relacionado
            $deleteUsuario = $database->query("DELETE FROM usuarios WHERE usuario = '$usuario'");
            if (!$deleteUsuario) {
                throw new Exception("Error al eliminar el usuario: " . $database->error);
            }

            // Eliminar el doctor si el usuario se eliminó con éxito
            $deleteDoctor = $database->query("DELETE FROM doctor WHERE docid = $id");
            if (!$deleteDoctor) {
                throw new Exception("Error al eliminar el doctor: " . $database->error);
            }

            // Redirige con éxito
            header("Location: doctores.php?delete_success=1");
            exit();
        } else {
            // Doctor no encontrado
            header("Location: doctores.php?error=doctor_not_found");
            exit();
        }
    } catch (Exception $e) {
        error_log("Error en borrar_doctor.php: " . $e->getMessage());
        header("Location: doctores.php?error=unexpected_error");
        exit();
    }
} else {
    // ID no válido
    header("Location: doctores.php?error=invalid_id");
    exit();
}
?>
