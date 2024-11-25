<?php
// Inicia una nueva sesión o reanuda la existente
session_start();

// Incluye el archivo de conexión a la base de datos
include('conexion_db.php');

// Verifica si se han enviado los datos del formulario ('usuario' y 'password')
if (isset($_POST['usuario']) && isset($_POST['password'])) {

    // Escapa caracteres especiales en una cadena para usarla en una consulta SQL, evitando inyecciones SQL
    $USUARIO = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $PASSWORD = mysqli_real_escape_string($conexion, $_POST['password']);

    // Prepara una consulta SQL para seleccionar el usuario y contraseña proporcionados
    $consulta = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ? AND password = ?");
    // Asocia las variables a los parámetros de la consulta preparada
    $consulta->bind_param('ss', $USUARIO, $PASSWORD);
    // Ejecuta la consulta
    $consulta->execute();
    // Obtiene el resultado de la consulta
    $resultado = $consulta->get_result();

    // Verifica si se encontró al menos una fila que coincida con la consulta
    if ($resultado->num_rows > 0) {
        // Obtiene la fila como un array asociativo
        $filas = $resultado->fetch_assoc();

        // Almacena información del usuario en la sesión
        $_SESSION['usuario'] = $USUARIO;
        $_SESSION['id_rol'] = $filas['id_rol'];

        // Redirige al usuario a la página correspondiente según su rol
        if ($filas['id_rol'] == 1) { // administrador
            header("Location: inicio_admin.html");
        } elseif ($filas['id_rol'] == 2) { // doctor
            header("Location: inicio_doctor.html");
        } elseif ($filas['id_rol'] == 3) { // paciente
            header("Location: inicio_paciente.html");
        } else {
            // Si el rol no es reconocido, redirige a la página de inicio y muestra un error            
            $_SESSION['error'] = "Error de autenticación";
            header("Location: index.html");
        }
    } else {
        // Si no se encontró ningún usuario que coincida, redirige a la página de inicio y muestra un error
        header("Location: index.html");
        $_SESSION['error'] = "Usuario o contraseña incorrectos";
    }

    // Cierra la consulta
    $consulta->close();
    // Cierra la conexión a la base de datos
    mysqli_close($conexion);
} else {
    // Si no se han enviado datos del formulario, redirige a la página de inicio y muestra un error
    header("Location: index.html");
    $_SESSION['error'] = "Por favor, ingrese sus credenciales";
}
