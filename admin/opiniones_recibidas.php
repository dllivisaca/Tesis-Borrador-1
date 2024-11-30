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
                <a href="opiniones_recibidas.php">
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
                        <p style="text-align: center; font-family: 'Poppins', Arial, sans-serif; font-weight: bold; color: #696969">Puntuación promedio de satisfacción</p>
                    </div>
                    <div style="width: 300px; margin: 0 auto;">
                        <canvas id="responseRateChart"></canvas>
                        <p style="text-align: center; font-family: 'Poppins', Arial, sans-serif; font-weight: bold; color: #696969">Tasa de respuesta</p>
                    </div>

                    <div style="width: 60%; margin: 0 auto; text-align: center; max-width: 500px;">
                        <canvas id="ratingsChart"></canvas>
                        <p style="font-family: 'Poppins', Arial, sans-serif; font-weight: bold; font-size: 16px; color: #696969;">
                            Calificaciones obtenidas
                        </p>
                    </div>
                </div>
            </div>

            <section class="comments">
                <p class="subheading-main1" style="color: #00b8d9; font-size: 15px;">Listado de comentarios</p>
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
                backgroundColor: ['#4A90E2', '#E0E0E0'], // Cambiar el color azul al más claro
                borderWidth: 0,
                cutout: '75%', // Grosor del arco ajustado para mayor grosor
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

        // Crear el gráfico tipo "Gauge" con el plugin local
        new Chart(gaugeCtx, {
            type: 'doughnut',
            data: data,
            options: options,
            plugins: [{
                id: 'customCenterText',
                afterDatasetsDraw(chart) {
                    const { ctx, chartArea: { width, height } } = chart;
                    ctx.save();
                    ctx.font = 'bold 20px Poppins';
                    ctx.fillStyle = '#007bff';
                    ctx.textAlign = 'center';
                    ctx.fillText(promedio.toFixed(2) + '/5', width / 2, height / 2 + 15);
                }
            }]
        });

        // Plugin para mostrar texto en el centro del Gauge
        const gaugeChartConfig = {
            type: 'doughnut',
            data: data, // Tus datos del gráfico Gauge
            options: options, // Opciones del gráfico Gauge
            plugins: [{
                id: 'customCenterText',
                afterDatasetsDraw(chart) {
                    const { ctx, chartArea: { width, height } } = chart;
                    ctx.save();
                    ctx.font = '20px Poppins';
                    ctx.fillStyle = '#007bff';
                    ctx.textAlign = 'center';
                    ctx.fillText(promedio.toFixed(2) + '/5', width / 2, height / 2 + 15);
                }
            }]
        };

        //////////////


        const responseCtx = document.getElementById('responseRateChart').getContext('2d');

        // Tasa de respuesta generada desde PHP
        const tasaRespuesta = <?php echo number_format($tasaRespuesta, 0); ?>;

        // Configuración de datos para el gráfico de Tasa de Respuesta
        const responseData = {
            datasets: [{
                data: [tasaRespuesta, 100 - tasaRespuesta], // Tasa de respuesta y restante
                backgroundColor: ['#4A90E2', '#E0E0E0'], // Azul y gris
                borderWidth: 0,
                cutout: '75%', // Grosor del arco
                circumference: 180,
                rotation: 270
            }]
        };

        const responseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: { enabled: false }, // Sin tooltips
                legend: { display: false }   // Sin leyendas
            }
        };

        // Crear gráfico tipo Gauge para Tasa de Respuesta
        new Chart(responseCtx, {
            type: 'doughnut',
            data: responseData,
            options: responseOptions,
            plugins: [{
                id: 'customResponseCenterText',
                afterDatasetsDraw(chart) {
                    const { ctx, chartArea: { width, height } } = chart;
                    ctx.save();
                    ctx.font = 'bold 20px Poppins'; // Negrita y fuente consistente
                    ctx.fillStyle = '#007bff'; // Azul
                    ctx.textAlign = 'center';
                    ctx.fillText(tasaRespuesta.toFixed(0) + '%', width / 2, height / 2 + 15);
                    ctx.restore();
                }
            }]
        });


 
    const ctx = document.getElementById('ratingsChart').getContext('2d');

    // Datos dinámicos generados desde PHP
    const ratingsData = <?php echo json_encode(array_values($ratings)); ?>; // Datos de calificaciones
    const labels = ['1', '2', '3', '4', '5']; // Etiquetas del eje X

    // Crear el gráfico
    new Chart(ctx, {
        type: 'bar', // Cambia a gráfico horizontal
        data: {
            labels: labels, // Etiquetas en el eje Y
            datasets: [{
                label: 'Calificaciones',
                data: ratingsData, // Datos
                backgroundColor: '#4A90E2', // Azul para las barras
                borderWidth: 0,
                barThickness: 15 // Grosor de las barras
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y', // Cambia el eje para barras horizontales
            plugins: {
                legend: { display: false }, // Sin leyendas
                tooltip: { enabled: true } // Activa o desactiva según tu diseño
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1 // Incrementos de 1
                    }
                },
                y: {
                    ticks: {
                        font: {
                            size: 12, // Tamaño del texto
                            weight: 'bold' // Negrita
                        },
                        color: '#007bff' // Color de las etiquetas
                    },
                    grid: {
                        display: false // Ocultar líneas de cuadrícula
                    }
                }
            }
        }
    });
</script>

</body>
</html>
