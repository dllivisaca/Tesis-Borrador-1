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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/opiniones_recibidas.css">
    
</head>
<body>
    <div class="container">
        <div class="menu">
            <div class="profile-container">
                <img src="../img/logo.png" alt="Logo" class="menu-logo">
                <p class="profile-title">Administrador</p>
            </div>
            <a href="../logout.php"><button class="btn-logout">Cerrar sesión</button></a>
            <div class="linea-separadora"></div>
            <div class="menu-links">
                <a href="dashboard.php" class="menu-link">Dashboard</a>
                <a href="doctores.php" class="menu-link">Doctores</a>
                <a href="pacientes.php" class="menu-link">Pacientes</a>
                <a href="horarios.php" class="menu-link">Horarios disponibles</a>
                <a href="citas.php" class="menu-link">Citas agendadas</a>
                <a href="opiniones_recibidas.php" class="menu-link menu-link-active">Opiniones recibidas</a>
            </div>
        </div>

        <div class="dash-body">
            <div class="header-actions">
            <!-- Sección izquierda: Botón Atrás y barra de búsqueda -->
            <div class="header-inline">
                <a href="citas.php">
                    <button class="btn-action">← Atrás</button>
                </a>
                <p class="heading-main12" style="margin: 0; font-size: 17px; color: rgb(49, 49, 49); align-self: left;">
                Opiniones recibidas
                </p>
            </div>
            <p class="subheading-main" style="color: #00b8d9; font-size: 15px;">Resumen general</p>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <div class="overview">
                <div class="summary">
                    <div style="width: 300px; margin: 0 auto;">
                        <canvas id="gaugeChart"></canvas>
                        <p style="text-align: center; font-family: 'Poppins', Arial, sans-serif; font-weight: bold;">Puntuación promedio de satisfacción</p>
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
                <form method="GET" action="opiniones_recibidas.php" class="filter-form">
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
       const gaugeCtx = document.getElementById('gaugeChart').getContext('2d');

        // Promedio dinámico generado desde PHP
        const promedio = <?php echo number_format($promedio, 2); ?>;

        // Configurar los datos del gráfico
        const data = {
            datasets: [{
                data: [promedio, 5 - promedio], // Promedio y espacio restante para llegar a 5
                backgroundColor: ['#4CAF50', '#E0E0E0'], // Colores
                borderWidth: 0,
                cutout: '85%',
                circumference: 180,
                rotation: 270
            }]
        };

        const options = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                tooltip: { enabled: false }, // Sin tooltips
                legend: { display: false }   // Sin leyendas
            }
        };

        // Crear el gráfico tipo "Gauge"
        new Chart(gaugeCtx, {
            type: 'doughnut',
            data: data,
            options: options
        });

        // Plugin para mostrar texto en el centro del Gauge
        Chart.register({
            id: 'customCenterText',
            afterDatasetsDraw(chart) {
                const { ctx, chartArea: { width, height } } = chart;
                ctx.save();
                ctx.font = '20px Poppins';
                ctx.fillStyle = '#333';
                ctx.textAlign = 'center';
                ctx.fillText(promedio.toFixed(2) + '/5', width / 2, height / 2 + 15);
            }
        });
 
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
