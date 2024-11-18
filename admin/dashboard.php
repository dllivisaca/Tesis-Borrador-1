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
    <style>
        .stat-box {
            display: inline-block;
            width: 22%;
            margin: 1%;
            padding: 15px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-box h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }

        .stat-box canvas {
            max-width: 100%;
            height: 150px;
        }

        .filter-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-container label {
            font-weight: bold;
        }

        .filter-container select {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .stats-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .profile-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .menu {
            width: 20%;
            background-color: #f7f8fa;
            padding: 20px;
            height: 100vh;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .menu-links a {
            display: block;
            padding: 10px;
            margin-bottom: 10px;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .menu-links a:hover, .menu-link-active {
            background-color: #007bff;
            color: white;
        }

        .dash-body {
            width: 75%;
            padding: 20px;
            margin-left: 25%;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
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
                    <p style="font-size: 36px; font-weight: bold; color: #333;"> <?php echo $totalCitas; ?> </p>
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
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
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
                responsive: true
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
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
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
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
