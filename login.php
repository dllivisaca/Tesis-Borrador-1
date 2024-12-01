<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
    <title>Inicio de sesión</title>
</head>
<body>
    <?php
    session_start();

    $_SESSION["usuario"] = "";
    $_SESSION["usuario_rol"] = "";
    
    date_default_timezone_set('America/Guayaquil');
    $fecha = date('Y-m-d');
    $_SESSION["date"] = $fecha;
    
    // Importar base de datos
    include("conexion_db.php");

    $error = '<label for="promter" class="form-label"></label>';

    if ($_POST) {
        $usuario = trim($_POST['usuario']);
        $password = trim($_POST['usuario_password']);

        // Verificar si el usuario existe en la tabla "usuarios"
        $result = $database->query("SELECT * FROM usuarios WHERE usuario='$usuario'");

        if ($result && $result->num_rows == 1) {
            $usuario_rol = $result->fetch_assoc()['usuario_rol'];
            $query = "";
            $ruta = "";

            // Determinar la tabla según el rol del usuario
            switch ($usuario_rol) {
                case 'pac':
                    $query = "SELECT pacpassword FROM paciente WHERE pacusuario = ?";
                    $ruta = 'paciente/citas.php';
                    break;
                case 'adm':
                    $query = "SELECT admpassword FROM administrador WHERE admusuario = ?";
                    $ruta = 'admin/dashboard.php';
                    break;
                case 'doc':
                    $query = "SELECT docpassword FROM doctor WHERE docusuario = ?";
                    $ruta = 'doctor/citas.php';
                    break;
                default:
                    $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Error: Rol no reconocido</label>';
                    break;
            }

            if ($query !== "") {
                // Preparar la consulta para obtener la contraseña hasheada
                if ($stmt = $database->prepare($query)) {
                    $stmt->bind_param("s", $usuario);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows == 1) {
                        $stmt->bind_result($hashed_password);
                        $stmt->fetch();

                        // Mostrar hash y contraseña para depuración
                        echo "<p style='color:blue;'>Hash recuperado: [" . htmlspecialchars($hashed_password) . "]</p>";
                        echo "<p style='color:blue;'>Contraseña ingresada: [" . htmlspecialchars($password) . "]</p>";

                        // Verificar la longitud de las cadenas para depuración
                        echo "<p style='color:blue;'>Longitud del hash: " . strlen($hashed_password) . "</p>";
                        echo "<p style='color:blue;'>Longitud de la contraseña ingresada: " . strlen($password) . "</p>";

                        // Verificar la contraseña con password_verify
                        if (password_verify($password, $hashed_password)) {
                            // Iniciar sesión y redirigir al usuario a la página correspondiente
                            $_SESSION['usuario'] = $usuario;
                            $_SESSION['usuario_rol'] = $usuario_rol;
                            header("Location: $ruta");
                            exit();
                        } else {
                            $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Error: Contraseña incorrecta</label>';
                        }
                    } else {
                        $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Error: No se encontró el usuario en la tabla específica</label>';
                    }
                    $stmt->close();
                } else {
                    $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Error: Fallo al preparar la consulta</label>';
                }
            }
        } else {
            $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">No existe cuenta creada para el usuario ingresado</label>';
        }
    }
    ?>

    <center>
        <div class="container">
            <table border="0" style="margin: 0;padding: 0;width: 100%;">
                <tr>
                    <td>
                        <img src="img/logo1.png" alt="Logo de la empresa" class="logo">
                        <p class="header-text">¡Nos alegra verte de nuevo!</p>
                    </td>
                </tr>
            <div class="form-body">
                <tr>
                    <td>
                        <p class="sub-text">Ingresa tus credenciales para continuar</p>
                    </td>
                </tr>
                <tr>
                    <form action="" method="POST">
                    <td class="label-td">
                        <label for="usuario" class="form-label">Usuario: </label>
                    </td>
                </tr>
                <tr>
                    <td class="label-td">
                        <input type="text" name="usuario" class="input-text" placeholder="Ingresa tu usuario" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-td">
                        <label for="usuario_password" class="form-label">Contraseña: </label>
                    </td>
                </tr>

                <tr>
                    <td class="label-td">
                        <input type="password" name="usuario_password" class="input-text" placeholder="Ingresa tu contraseña" required>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php echo $error; ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <input type="submit" value="Iniciar sesión" class="login-btn btn-primary btn">
                    </td>
                </tr>
            </div>
                <tr>
                    <td>
                        <br>
                        <label for="" class="sub-text" style="font-weight: 280;">¿Eres un paciente y no tienes cuenta?</label>
                        <a href="registro.php" class="hover-link1 non-style-link">Regístrate</a>
                        <br><br><br>
                    </td>
                </tr>
                            
                    </form>
            </table>
        </div>
    </center>
</body>
</html>
