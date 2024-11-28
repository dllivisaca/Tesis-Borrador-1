<?php
session_start();

// Mostrar mensajes según los parámetros en la URL
if (isset($_GET['delete_success']) && $_GET['delete_success'] == 1) {
    echo '<script>alert("Doctor eliminado con éxito.");</script>';
} elseif (isset($_GET['delete_success']) && $_GET['delete_success'] == 0) {
    echo '<script>alert("Error al eliminar el doctor.");</script>';
} elseif (isset($_GET['error'])) {
    if ($_GET['error'] == 'doctor_not_found') {
        echo '<script>alert("Doctor no encontrado.");</script>';
    } elseif ($_GET['error'] == 'invalid_id') {
        echo '<script>alert("ID inválido.");</script>';
    }
}

if (isset($_SESSION["usuario"])) {
    if ($_SESSION["usuario"] == "" || $_SESSION["usuario_rol"] != 'adm') {
        header("Location: ../login.php");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}

if ($_GET) {
    // Importar la base de datos
    include("../conexion_db.php");

    if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
        
        $id = intval($_GET["id"]); // Asegúrate de que el ID sea un número
        

        // Consultar el usuario relacionado con el doctor
        $result001 = $database->query("SELECT docusuario FROM doctor WHERE docid = $id");

        if (!$result001) {
            die("Error en la consulta SQL: " . $database->error);
        }
        if ($result001->num_rows === 0) {
            die("No se encontró ningún doctor con el ID: $id");
        }

        if ($result001 && $result001->num_rows > 0) {
            $usuario = $result001->fetch_assoc()["docusuario"];

            // Eliminar primero el usuario relacionado
            $deleteUsuario = $database->query("DELETE FROM usuarios WHERE usuario = '$usuario'");

            if ($deleteUsuario) {
                // Eliminar el doctor solo si el usuario fue eliminado con éxito
                $deleteDoctor = $database->query("DELETE FROM doctor WHERE docid = $id");

                if ($deleteDoctor) {
                    // Redirigir a la lista de doctores con éxito
                    header("Location: doctores.php?delete_success=1");
                    exit();
                } else {
                    // Error al eliminar el doctor
                    header("Location: doctores.php?delete_success=0");
                    exit();
                }
            } else {
                // Error al eliminar el usuario
                header("Location: doctores.php?delete_success=0");
                exit();
            }
        } else {
            // Doctor no encontrado
            header("Location: doctores.php?error=doctor_not_found");
            exit();
        }
    } else {
        // Parámetro `id` no válido
        header("Location: doctores.php?error=invalid_id");
        exit();
    }
}
?>
