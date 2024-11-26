<?php
session_start();

if (isset($_SESSION["usuario"])) {
    if ($_SESSION["usuario"] == "" || $_SESSION['usuario_rol'] != 'adm') {
        header("location: ../login.php");
    } else {
        $usuario = $_SESSION["usuario"];
    }
} else {
    header("location: ../login.php");
}

// Importar la base de datos
include("../conexion_db.php");

// Total de citas
$totalCitasQuery = "SELECT COUNT(*) AS total FROM citas";
$totalCitasResult = $database->query($totalCitasQuery);
$totalCitas = $totalCitasResult->fetch_assoc()["total"];

// Número de citas por especialidad
$citasPorEspecialidadQuery = "SELECT especialidades.espnombre, COUNT(*) AS cantidad
                            FROM citas
                            INNER JOIN doctor ON citas.docid = doctor.docid
                            INNER JOIN especialidades ON doctor.especialidades = especialidades.id
                            GROUP BY especialidades.espnombre";
$citasPorEspecialidadResult = $database->query($citasPorEspecialidadQuery);
$citasPorEspecialidad = [];
while ($row = $citasPorEspecialidadResult->fetch_assoc()) {
    $citasPorEspecialidad[] = $row;
}

// Número de citas por doctor
$citasPorDoctorQuery = "SELECT doctor.docnombre, COUNT(*) AS cantidad
                        FROM citas
                        INNER JOIN doctor ON citas.docid = doctor.docid
                        GROUP BY doctor.docnombre";
$citasPorDoctorResult = $database->query($citasPorDoctorQuery);
$citasPorDoctor = [];
while ($row = $citasPorDoctorResult->fetch_assoc()) {
    $citasPorDoctor[] = $row;
}

// Top 3 horarios con mayor actividad
$horariosConMayorActividadQuery = "SELECT CONCAT(hora_inicio, ' - ', hora_fin) AS horario, COUNT(*) AS cantidad
                                   FROM citas
                                   GROUP BY hora_inicio, hora_fin
                                   ORDER BY cantidad DESC
                                   LIMIT 3";
$horariosConMayorActividadResult = $database->query($horariosConMayorActividadQuery);
$horariosConMayorActividad = [];
while ($row = $horariosConMayorActividadResult->fetch_assoc()) {
    $horariosConMayorActividad[] = $row;
}

// Top 2 días con mayor actividad
$diasConMayorActividadQuery = "SELECT DATE_FORMAT(fecha, '%W') AS dia, COUNT(*) AS cantidad
                               FROM citas
                               GROUP BY dia
                               ORDER BY cantidad DESC
                               LIMIT 2";
$diasConMayorActividadResult = $database->query($diasConMayorActividadQuery);
$diasConMayorActividad = [];
while ($row = $diasConMayorActividadResult->fetch_assoc()) {
    $diasConMayorActividad[] = $row;
}

// Traducción de días al español
$diasEnEspanol = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
];

// Reemplazar días en inglés por días en español
foreach ($diasConMayorActividad as &$dia) {
    $dia['dia'] = $diasEnEspanol[$dia['dia']];
}
unset($dia); // Limpiar referencia
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Dashboard Administrativo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9; /* Fondo más claro */
            color: #333; /* Texto más oscuro */
        }

        .container {
            display: flex;
            flex-direction: row;
            height: 100vh;
        }

        .menu {
            width: 18%; /* Menú más estrecho */
            background-color: #f4f4f4;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .menu .profile-title {
            font-size: 20px;
            color: #007bff; /* Azul para el título */
            text-align: center;
            margin-bottom: 15px;
        }

        .menu a {
            display: block;
            color: #333;
            text-decoration: none;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .menu a:hover,
        .menu-link-active {
            background-color: #007bff;
            color: white;
        }

        .dash-body {
            flex-grow: 1;
            padding: 20px;
        }

        .dash-body h2 {
            font-size: 22px;
            color: #007bff;
            margin-bottom: 20px;
            text-align: center;
        }

        .filter-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-container select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px; /* Más espacio entre los elementos */
            margin-top: 20px;
        }

        .stat-box {
            background: #ffffff;
            border: 1px solid #eaeaea;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 350px; /* Limitar el ancho de cada caja */
            margin: 0 auto; /* Centrar las cajas */
        }

        .stat-box h3 {
            font-size: 16px; /* Tamaño reducido */
            font-weight: 600;
            color: #555555;
            margin-bottom: 10px;
        }

        .stat-box p {
            font-size: 50px; /* Tamaño grande */
            color: #007bff; /* Azul */
            font-weight: bold;
            margin: 0;
        }

        .stat-box canvas {
            max-width: 300px; /* Ajusta el ancho máximo del gráfico */
            height: auto; /* Permite que el gráfico mantenga proporciones naturales */
            aspect-ratio: 1; /* Define una relación de aspecto razonable */
            margin: 0 auto; /* Centrar el gráfico */
        }

        .btn-logout {
            width: 100%;
            background-color: #ff4c4c;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-logout:hover {
            background-color: #d9534f;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="menu">
            <p class="profile-title">Administrador</p>
            <a href="../logout.php"><button class="logout-btn">Cerrar sesión</button></a>
            <div class="menu-links">
                <a href="dashboard.php" class="menu-link menu-link-active">Dashboard</a>
                <a href="doctores.php" class="menu-link">Doctores</a>
                <a href="pacientes.php" class="menu-link">Pacientes</a>
                <a href="horarios.php" class="menu-link">Horarios disponibles</a>
                <a href="citas.php" class="menu-link">Citas agendadas</a>
                <a href="opiniones.php" class="menu-link">Opiniones recibidas</a>
            </div>
        </div>
        <div class="dash-body">
            <h2>Analíticas</h2>

            <div class="filter-container">
                <label for="year">Año:</label>
                <select id="year" name="year">
                    <option value="">Año</option>
                </select>
                <label for="month">Mes:</label>
                <select id="month" name="month">
                    <option value="">Mes</option>
                </select>
                <label for="day">Día:</label>
                <select id="day" name="day">
                    <option value="">Día</option>
                </select>
            </div>

            <div class="stats-container">
                <div class="stat-box">
                    <h3>Total de citas</h3>
                    <p><?php echo $totalCitas; ?></p>
                </div>
                <div class="stat-box">
                    <h3>Número de citas por especialidad</h3>
                    <canvas id="citasPorEspecialidadChart"></canvas>
                </div>
                <div class="stat-box">
                    <h3>Número de citas por doctor</h3>
                    <canvas id="citasPorDoctorChart"></canvas>
                </div>
                <div class="stat-box">
                    <h3>Top 3 horarios con mayor actividad</h3>
                    <canvas id="horariosConMayorActividadChart"></canvas>
                </div>
                <div class="stat-box">
                    <h3>Top 2 días con mayor actividad</h3>
                    <canvas id="diasConMayorActividadChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Número de citas por especialidad
        const citasPorEspecialidadCtx = document.getElementById('citasPorEspecialidadChart').getContext('2d');
        new Chart(citasPorEspecialidadCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($citasPorEspecialidad, 'espnombre')); ?>,
                datasets: [{
                    label: 'Número de citas',
                    data: <?php echo json_encode(array_column($citasPorEspecialidad, 'cantidad')); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2, // Ancho del borde
                    borderRadius: 10 // Bordes redondeados
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true, // Mantiene la relación de aspecto
                aspectRatio: 2, // Relación ancho/alto personalizada
                scales: {
                    x: {
                        grid: {
                            display: false // Oculta líneas de cuadrícula en eje X
                        },
                        ticks: {
                            maxRotation: 0, // Evita inclinación de las etiquetas
                            minRotation: 0
                        }
                    },
                    y: {
                        grid: {
                            color: '#e0e0e0' // Color de las líneas de cuadrícula
                        },
                        beginAtZero: true // Comienza en 0
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    }
                }
            }
        });

        // Número de citas por doctor
        const citasPorDoctorCtx = document.getElementById('citasPorDoctorChart').getContext('2d');
        new Chart(citasPorDoctorCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($citasPorDoctor, 'docnombre')); ?>,
                datasets: [{
                    label: 'Número de citas',
                    data: <?php echo json_encode(array_column($citasPorDoctor, 'cantidad')); ?>,
                    backgroundColor: ['rgba(255, 99, 132, 0.5)', 'rgba(54, 162, 235, 0.5)', 'rgba(255, 206, 86, 0.5)'],
                    borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true, // Mantener relación de aspecto
                aspectRatio: 1.8, // Relación ancho/alto (para gráficos de pastel)
                plugins: {
                    legend: {
                        position: 'top' // Ubicar la leyenda en la parte superior
                    }
                }
            }
        });

        // Top 3 horarios con mayor actividad
        const horariosConMayorActividadCtx = document.getElementById('horariosConMayorActividadChart').getContext('2d');
        new Chart(horariosConMayorActividadCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($horariosConMayorActividad, 'horario')); ?>,
                datasets: [{
                    label: 'Número de citas',
                    data: <?php echo json_encode(array_column($horariosConMayorActividad, 'cantidad')); ?>,
                    backgroundColor: 'rgba(153, 102, 255, 0.5)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1,
                    borderRadius: 10 
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1, // Incremento de 1 en el eje Y
                            callback: function(value) {
                                return Number.isInteger(value) ? value : ''; // Solo mostrar números enteros
                            }
                        }
                    }
                }
            }
        });

        // Top 2 días con mayor actividad
        const diasConMayorActividadCtx = document.getElementById('diasConMayorActividadChart').getContext('2d');
        new Chart(diasConMayorActividadCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($diasConMayorActividad, 'dia')); ?>,
                datasets: [{
                    label: 'Número de citas',
                    data: <?php echo json_encode(array_column($diasConMayorActividad, 'cantidad')); ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.5)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1,
                    borderRadius: 10 
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1, // Incremento de 1 en el eje Y
                            callback: function(value) {
                                return Number.isInteger(value) ? value : ''; // Solo mostrar números enteros
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
