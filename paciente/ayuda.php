<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/base.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .dash-body {
            flex-grow: 1;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .content {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px;
            max-width: 1000px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.8s ease-in-out;
        }
        .content h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .content p {
            font-size: 1rem;
            color: #555;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .whatsapp-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        .whatsapp-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 1rem;
            color: #25d366;
            font-weight: 600;
        }
        .whatsapp-icon img {
            width: 40px;
            height: 40px;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <title>Ayuda</title>
</head>
<body>
    <?php
    error_reporting(E_ERROR | E_PARSE);

    date_default_timezone_set('America/Guayaquil');

    session_start();

    if (isset($_SESSION["usuario"])) {
        if ($_SESSION["usuario"] == "" || $_SESSION['usuario_rol'] != 'pac') {
            header("location: ../login.php");
        } else {
            $usuario = $_SESSION["usuario"];
        }
    } else {
        header("location: ../login.php");
    }

    // Importar la base de datos
    include("../conexion_db.php");
    $userrow = $database->query("SELECT * FROM paciente WHERE pacusuario='$usuario'");
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch["pacid"];
    $username = $userfetch["pacnombre"];
    ?>

    <div class="container">
        <div class="menu">
            <div class="profile-container">
                <img src="../img/logo.png" alt="Logo" class="menu-logo">
                <p class="profile-title"><?php echo substr($username, 0, 13); ?>..</p>
            </div>

            <a href="../logout.php"><button class="btn-logout">Cerrar sesión</button></a>
            <div class="linea-separadora"></div>
            <div class="menu-links">
                <a href="citas.php" class="menu-link">Citas agendadas</a>
                <a href="horarios.php" class="menu-link">Horarios disponibles</a>
                <a href="ayuda.php" class="menu-link-active">Ayuda</a>
            </div>
        </div>

        <div class="dash-body">
            <div class="content">
                <h1>Ayuda</h1>
                <p>Gestiona tus datos y citas en el sistema de manera sencilla:</p>
                <ul style="text-align: left; color: #555; line-height: 1.5; font-size: 0.95rem; padding-left: 20px;">
                    <li><strong>Trámite disponible:</strong> Acceso o actualización de datos personales.</li>
                    <li><strong>Requisitos:</strong> Usuario registrado.</li>
                    <li><strong>Procedimiento:</strong>  
                        <ul style="padding-left: 15px;">
                            <li>Haz clic en el botón de WhatsApp.</li>
                            <li>Solicita acceso o actualización de datos.</li>
                            <li>Adjunta los justificativos si aplica.</li>
                        </ul>
                    </li>
                </ul>
                <h2 style="color: #333; font-size: 1.3rem; margin-top: 1.5rem;">Gestión de citas</h2>
                <ul style="text-align: left; color: #555; line-height: 1.5; font-size: 0.95rem; padding-left: 20px;">
                    <li><strong>Procedimiento:</strong>  
                        <ul style="padding-left: 15px;">
                            <li>Inicia sesión y accede a "Horarios disponibles".</li>
                            <li>Visualiza la lista de doctores, sus especialidades y horarios
                            disponibles.</li>
                            <li>Selecciona el doctor de tu preferencia haciendo clic en el botón "+
                            Agendar cita" correspondiente.</li>
                            <li>Elige la fecha y hora disponibles, luego confirma la cita haciendo
                            clic en "+ Agendar cita".</li>
                        </ul>
                    </li>
                    <li><strong>Requisitos:</strong> Usuario registrado.</li>
                    <li><a href="manual_usuario.pdf" target="_blank" style="color: #007BFF;">Consulta el manual de usuario aquí</a>.</li>
                </ul>

                <p>¿Necesitas ayuda? Contáctanos por WhatsApp:</p>
                <div class="whatsapp-container">
                    <a href="https://wa.me/593939034743" target="_blank" class="whatsapp-icon">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" style="width: 30px; height: 30px;">
                        <span>Contáctanos por WhatsApp</span>
                    </a>
                </div>
                
            </div>
        </div>
    </div>
</body>
</html>
