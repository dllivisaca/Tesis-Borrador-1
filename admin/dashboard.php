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
$totalCitasQuery = "SELECT COUNT(*) AS total FROM citas WHERE estado = 'finalizada'";
$totalCitasResult = $database->query($totalCitasQuery);
$totalCitas = $totalCitasResult->fetch_assoc()["total"];

// Número de citas por especialidad
$citasPorEspecialidadQuery = "SELECT especialidades.espnombre, COUNT(*) AS cantidad
                            FROM citas
                            INNER JOIN doctor ON citas.docid = doctor.docid
                            INNER JOIN especialidades ON doctor.especialidades = especialidades.id
                            WHERE citas.estado = 'finalizada'
                            GROUP BY especialidades.espnombre";
$citasPorEspecialidadResult = $database->query($citasPorEspecialidadQuery);
$citasPorEspecialidad = [];
while ($row = $citasPorEspecialidadResult->fetch_assoc()) {
    $citasPorEspecialidad[] = $row;
}

// Abreviar nombres largos en las especialidades
$longitudMaxima = 10; // Cambia este valor según lo necesario
foreach ($citasPorEspecialidad as &$especialidad) {
    $especialidad['nombre_completo'] = $especialidad['espnombre']; // Guardar nombre completo
    if (strlen($especialidad['espnombre']) > $longitudMaxima) {
        $especialidad['espnombre'] = substr($especialidad['espnombre'], 0, $longitudMaxima - 3) . '...'; // Abreviar
    }
}
unset($especialidad); // Limpiar referencia

// Número de citas por doctor
$citasPorDoctorQuery = "SELECT doctor.docnombre, COUNT(*) AS cantidad
                        FROM citas
                        INNER JOIN doctor ON citas.docid = doctor.docid
                        WHERE citas.estado = 'finalizada'
                        GROUP BY doctor.docnombre";
$citasPorDoctorResult = $database->query($citasPorDoctorQuery);
$citasPorDoctor = [];
while ($row = $citasPorDoctorResult->fetch_assoc()) {
    $citasPorDoctor[] = $row;
}

// Top 3 horarios con mayor actividad
$horariosConMayorActividadQuery = "SELECT CONCAT(TIME_FORMAT(hora_inicio, '%H:%i'), ' - ', TIME_FORMAT(hora_fin, '%H:%i')) AS horario, COUNT(*) AS cantidad
                                   FROM citas
                                   WHERE estado = 'finalizada'
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
                               WHERE estado = 'finalizada'
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
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/admin/dashboard.css">
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
                <a href="dashboard.php" class="menu-link menu-link-active">Dashboard</a>
                <a href="doctores.php" class="menu-link">Doctores</a>
                <a href="pacientes.php" class="menu-link">Pacientes</a>
                <a href="horarios.php" class="menu-link">Horarios disponibles</a>
                <a href="citas.php" class="menu-link">Citas agendadas</a>
                <a href="opiniones_recibidas.php" class="menu-link">Opiniones recibidas</a>
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

                <!-- Botón de filtrar -->
                <button id="filterButton" class="btn-filter">Filtrar</button>

                <!-- Botón de limpiar filtros -->
                <button id="clearFiltersButton" class="btn-filter">Limpiar filtros</button>
            </div>

            <div class="stats-container">
                <div class="stat-box">
                    <h3>Total de citas finalizadas</h3>
                    <p id="totalCitas"><?php echo $totalCitas; ?></p>
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
         // Convertir los datos de PHP a JavaScript
        const citasPorEspecialidad = <?php echo json_encode($citasPorEspecialidad); ?>;

        // Número de citas por especialidad
        const citasPorEspecialidadCtx = document.getElementById('citasPorEspecialidadChart').getContext('2d');
        const citasPorEspecialidadChart = new Chart(citasPorEspecialidadCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($citasPorEspecialidad, 'espnombre')); ?>,
                datasets: [{
                    label: 'Número de citas',
                    data: <?php echo json_encode(array_column($citasPorEspecialidad, 'cantidad')); ?>,
                    backgroundColor: 'rgba(128, 128, 128, 0.5)',
                    borderColor: 'rgba(64, 64, 64, 1)',
                    borderWidth: 2,
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: {
                        ticks: {
                            maxRotation: 0,
                            minRotation: 0,
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        grid: {
                            color: '#e0e0e0'
                        },
                        beginAtZero: true
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function (tooltipItems) {
                                const index = tooltipItems[0].dataIndex;
                                const nombreCompleto = citasPorEspecialidad[index]?.nombre_completo || 'Desconocido';
                                return `${nombreCompleto}`; // Solo muestra el nombre completo como título
                            },
                            label: function (tooltipItem) {
                                const cantidad = tooltipItem.raw; // Obtén el valor del dato
                                return `Número de citas: ${cantidad}`; // Segunda línea con el número de citas
                            }
                        }
                    },
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
        const citasPorDoctorChart = new Chart(citasPorDoctorCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($citasPorDoctor, 'docnombre')); ?>,
                datasets: [{
                    label: 'Número de citas',
                    data: <?php echo json_encode(array_column($citasPorDoctor, 'cantidad')); ?>,
                    backgroundColor: ['rgba(192, 192, 192, 0.5)', 'rgba(160, 160, 160, 0.5)', 'rgba(128, 128, 128, 0.5)'],
                    borderColor: ['rgba(96, 96, 96, 1)', 'rgba(64, 64, 64, 1)', 'rgba(32, 32, 32, 1)'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true, // Mantener relación de aspecto
                aspectRatio: 1.5, // Ajustar proporción ancho/alto
                plugins: {
                    legend: { position: 'top' } // Posición de la leyenda
                }
            }
        });

        // Top 3 horarios con mayor actividad
        const horariosConMayorActividadCtx = document.getElementById('horariosConMayorActividadChart').getContext('2d');
        const horariosConMayorActividadChart = new Chart(horariosConMayorActividadCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($horariosConMayorActividad, 'horario')); ?>,
                datasets: [{
                    label: 'Número de citas',
                    data: <?php echo json_encode(array_column($horariosConMayorActividad, 'cantidad')); ?>,
                    backgroundColor: 'rgba(160, 160, 160, 0.5)', // Escala de gris
                    borderColor: 'rgba(96, 96, 96, 1)', // Gris oscuro
                    borderWidth: 2,
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: {
                        ticks: {
                            maxRotation: 0,
                            minRotation: 0,
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true, // Comienza desde 0
                        ticks: {
                            callback: function(value) {
                                // Mostrar solo si el valor es un número entero
                                if (Number.isInteger(value)) {
                                    return value;
                                }
                                return null; // No mostrar valores no enteros
                            },
                            stepSize: 1, // Incremento de 1 entre valores
                            
                            font: {
                                size: 12, // Tamaño de la fuente
                                
                            }
                        },
                        grid: {
                            color: '#e0e0e0' // Líneas de cuadrícula en gris claro
                        }
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

        // Top 2 días con mayor actividad
        const diasConMayorActividadCtx = document.getElementById('diasConMayorActividadChart').getContext('2d');
        const diasConMayorActividadChart = new Chart(diasConMayorActividadCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($diasConMayorActividad, 'dia')); ?>,
                datasets: [{
                    label: 'Número de citas',
                    data: <?php echo json_encode(array_column($diasConMayorActividad, 'cantidad')); ?>,
                    backgroundColor: 'rgba(192, 192, 192, 0.5)', // Escala de gris
                    borderColor: 'rgba(128, 128, 128, 1)', // Gris oscuro
                    borderWidth: 2,
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e0e0e0'
                        }
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

        document.addEventListener("DOMContentLoaded", function () {
            const yearSelect = document.getElementById("year");
            const monthSelect = document.getElementById("month");
            const daySelect = document.getElementById("day");

            // Limitar opciones del filtro de año a solo 2024
            const option = document.createElement("option");
            option.value = 2024;
            option.textContent = 2024;
            yearSelect.appendChild(option);

            // Lista de nombres de los meses
            const monthNames = [
                "Enero",
                "Febrero",
                "Marzo",
                "Abril",
                "Mayo",
                "Junio",
                "Julio",
                "Agosto",
                "Septiembre",
                "Octubre",
                "Noviembre",
                "Diciembre"
            ];

            // Poblar los meses con nombres
            monthNames.forEach((month, index) => {
                const option = document.createElement("option");
                option.value = index + 1; // Los meses comienzan en 1
                option.textContent = month;
                monthSelect.appendChild(option);
            });

            // Función para actualizar los días según el año y mes seleccionados
            function updateDays() {
                const year = parseInt(yearSelect.value);
                const month = parseInt(monthSelect.value);

                // Limpiar los días anteriores
                daySelect.innerHTML = '<option value="">Día</option>';

                if (!isNaN(year) && !isNaN(month)) {
                    const daysInMonth = new Date(year, month, 0).getDate(); // Obtiene el último día del mes
                    for (let day = 1; day <= daysInMonth; day++) {
                        const option = document.createElement("option");
                        option.value = day;
                        option.textContent = day;
                        daySelect.appendChild(option);
                    }
                }
            }

            // Escuchar cambios en año y mes
            yearSelect.addEventListener("change", updateDays);
            monthSelect.addEventListener("change", updateDays);
        });

        document.addEventListener("DOMContentLoaded", function () {
            const filterButton = document.getElementById("filterButton");
            const yearSelect = document.getElementById("year");
            const monthSelect = document.getElementById("month");
            const daySelect = document.getElementById("day");

            filterButton.addEventListener("click", function () {
                console.log("Botón 'Filtrar' presionado"); 
                const year = yearSelect.value;
                const month = monthSelect.value;
                const day = daySelect.value;

                if (!year && !month && !day) {
                    alert("Por favor, selecciona al menos un filtro.");
                    return;
                }

                fetch("filtrar_citas.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ year, month, day }),
                })
                    .then(response => response.text())
                    .then(text => {
                        try {
                            const data = JSON.parse(text);
                            console.log("Respuesta del servidor:", data);
                            if (data.success) {
                                actualizarGraficos(data);
                            } else {
                                alert(data.message || "No se encontraron datos para los filtros seleccionados.");
                            }
                        } catch (err) {
                            console.error("Error al parsear JSON:", err);
                            console.error("Texto de respuesta:", text);
                            alert("Ocurrió un error al procesar la respuesta del servidor.");
                        }
                    })
                    .catch(error => console.error("Error al filtrar los datos:", error));
            });

             // Botón "Limpiar filtros"
            clearFiltersButton.addEventListener("click", function () {
                location.reload(); // Recargar la página
            });

            function actualizarGraficos(data) {
                // Actualizar "Total de citas"
                document.querySelector('#totalCitas').textContent = data.totalCitas;

                // Actualizar "Número de citas por especialidad"
                citasPorEspecialidadChart.data.labels = data.citasPorEspecialidad.labels;
                citasPorEspecialidadChart.data.datasets[0].data = data.citasPorEspecialidad.data;
                citasPorEspecialidadChart.update();

                // Actualizar "Número de citas por doctor"
                citasPorDoctorChart.data.labels = data.citasPorDoctor.labels;
                citasPorDoctorChart.data.datasets[0].data = data.citasPorDoctor.data;
                citasPorDoctorChart.update();

                // Actualizar "Top 3 horarios con mayor actividad"
                horariosConMayorActividadChart.data.labels = data.horariosConMayorActividad.labels;
                horariosConMayorActividadChart.data.datasets[0].data = data.horariosConMayorActividad.data;
                horariosConMayorActividadChart.update();

                // Actualizar "Top 2 días con mayor actividad"
                diasConMayorActividadChart.data.labels = data.diasConMayorActividad.labels;
                diasConMayorActividadChart.data.datasets[0].data = data.diasConMayorActividad.data;
                diasConMayorActividadChart.update();
            }
        });

        

    </script>
</body>
</html>