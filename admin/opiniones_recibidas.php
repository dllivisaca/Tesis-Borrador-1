<?php

include("../conexion_db.php");


// Obtener puntuación promedio
$avgQuery = "SELECT AVG(calificacion) AS promedio FROM respuestas_encuestas WHERE estado = 'completado' AND calificacion IS NOT NULL";
$avgResult = $database->query($avgQuery);
$promedio = $avgResult->fetch_assoc()['promedio'] ?? 0;

// Obtener tasa de respuesta
$totalEncuestasQuery = "SELECT COUNT(*) AS total FROM respuestas_encuestas";
$totalCompletadoQuery = "SELECT COUNT(*) AS completado FROM respuestas_encuestas WHERE estado = 'completado'";
$totalEncuestas = $database->query($totalEncuestasQuery)->fetch_assoc()['total'] ?? 1;
$totalCompletado = $database->query($totalCompletadoQuery)->fetch_assoc()['completado'] ?? 0;
$tasaRespuesta = ($totalCompletado / $totalEncuestas) * 100;

// Obtener distribución de calificaciones
$ratingsQuery = "SELECT calificacion, COUNT(*) AS cantidad FROM respuestas_encuestas WHERE estado = 'completado' AND calificacion IS NOT NULL GROUP BY calificacion ORDER BY calificacion";
$ratingsResult = $database->query($ratingsQuery);
$ratings = [];
for ($i = 1; $i <= 5; $i++) {
    $ratings[$i] = 0; // Inicializar con 0 para todas las calificaciones
}
while ($row = $ratingsResult->fetch_assoc()) {
    $ratings[$row['calificacion']] = $row['cantidad'];
}

// Obtener comentarios
$dateFilter = $_GET['date'] ?? null;
$keywordFilter = $_GET['keyword'] ?? null;

$commentsQuery = "SELECT comentario, fecha_respuesta FROM respuestas_encuestas WHERE estado = 'completado' AND comentario IS NOT NULL";
if ($dateFilter) {
    $commentsQuery .= " AND DATE(fecha_respuesta) = '$dateFilter'";
}
if ($keywordFilter) {
    $commentsQuery .= " AND comentario LIKE '%$keywordFilter%'";
}
$commentsQuery .= " ORDER BY fecha_respuesta DESC";
$commentsResult = $database->query($commentsQuery);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opiniones Recibidas</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .container {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        .sidebar {
            width: 20%;
            background-color: #f4f4f4;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar .profile {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar .profile-logo img {
            width: 100px;
            border-radius: 50%;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 10px 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #333;
        }

        .sidebar ul li.active a {
            font-weight: bold;
            color: #007bff;
        }

        .main-content {
            width: 80%;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .summary {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .summary div {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 30%;
        }

        .comments {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .filter-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .comments-list .comment {
            background: #f9f9f9;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
        }

    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="profile">
                <div class="profile-logo">
                    <img src="logo.png" alt="Logo">
                </div>
                <p>Administrador</p>
                <button class="btn-logout">Cerrar sesión</button>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="doctores.php">Doctores</a></li>
                    <li><a href="pacientes.php">Pacientes</a></li>
                    <li><a href="horarios.php">Horarios disponibles</a></li>
                    <li><a href="citas.php">Citas agendadas</a></li>
                    <li class="active"><a href="opiniones.php">Opiniones recibidas</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header>
                <button class="btn-back">Atrás</button>
                <h1>Opiniones recibidas</h1>
            </header>

            <div class="overview">
                <div class="summary">
                    <div>
                        <h2><?php echo number_format($promedio, 2); ?>/5</h2>
                        <p>Puntuación promedio de satisfacción</p>
                    </div>
                    <div>
                        <h2><?php echo number_format($tasaRespuesta, 0); ?>%</h2>
                        <p>Tasa de respuesta</p>
                    </div>
                    <div>
                        <h2>Calificaciones obtenidas</h2>
                        <canvas id="ratingsChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <section class="comments">
                <h2>Listado de comentarios</h2>
                <form method="GET" action="opiniones.php" class="filter-form">
                    <label for="date">Fecha:</label>
                    <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($dateFilter ?? ''); ?>">

                    <label for="keyword">Palabra clave:</label>
                    <input type="text" name="keyword" id="keyword" placeholder="Escribe una palabra clave" value="<?php echo htmlspecialchars($keywordFilter ?? ''); ?>">

                    <button type="submit" class="btn-search">Buscar</button>
                </form>

                <div class="comments-list">
                    <?php
                    if ($commentsResult->num_rows > 0) {
                        while ($comment = $commentsResult->fetch_assoc()) {
                            echo '<div class="comment">';
                            echo '<p>' . htmlspecialchars($comment['comentario']) . '</p>';
                            echo '<small>' . date('d/m/Y H:i', strtotime($comment['fecha_respuesta'])) . '</small>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No se encontraron comentarios.</p>';
                    }
                    ?>
                </div>
            </section>
        </main>
    </div>
    <script src="chart.js"></script>
    <script src="script.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    const ctx = document.getElementById('ratingsChart').getContext('2d');

    // Datos dinámicos generados desde PHP
    const ratingsData = <?php echo json_encode(array_values($ratings)); ?>; // Datos de calificaciones
    const labels = ['1', '2', '3', '4', '5']; // Etiquetas del eje X

    // Crear el gráfico
    new Chart(ctx, {
        type: 'bar', // Tipo de gráfico
        data: {
            labels: labels, // Etiquetas
            datasets: [{
                label: 'Calificaciones',
                data: ratingsData, // Datos
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1, // Incrementos de 1 en el eje Y
                    callback: function(value) {
                        return value; // Mostrar solo números enteros
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>
