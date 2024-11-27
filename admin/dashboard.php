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
                        GROUP BY doctor.docnombre";
$citasPorDoctorResult = $database->query($citasPorDoctorQuery);
$citasPorDoctor = [];
while ($row = $citasPorDoctorResult->fetch_assoc()) {
    $citasPorDoctor[] = $row;
}

// Top 3 horarios con mayor actividad
$horariosConMayorActividadQuery = "SELECT CONCAT(TIME_FORMAT(hora_inicio, '%H:%i'), ' - ', TIME_FORMAT(hora_fin, '%H:%i')) AS horario, COUNT(*) AS cantidad
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
            background-color: #ffffff; /* Cambiar fondo a blanco */
            color: #333; /* Texto más oscuro */
        }

        .container {
            display: flex;
            flex-direction: row;
            height: 100vh;
            background-color: #ffffff; /* Fondo blanco para toda la estructura */
        }

        .menu {
            width: 15%; /* Aumentar ligeramente el ancho del menú */
            background-color: #ffffff;
            padding: 20px; /* Espaciado interno reducido */
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .profile-title {
            font-size: 14px; /* Reducir tamaño de la fuente */
            color: #000;
            font-weight: bold;
        }

        .menu .profile-title {
            font-size: 17px;
            color: #000; /* Azul */
            font-weight: bold;
            line-height: 1; /* Ajusta la altura de la línea para que no genere espacio extra */
            margin: 0; /* Elimina cualquier margen */
            padding: 0; /* Elimina cualquier relleno */
            display: inline-block; /* Para evitar que se expanda innecesariamente */
            vertical-align: middle; /* Asegura el alineamiento vertical */
        }

        .menu a {
            display: block;
            text-decoration: none;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .menu-links {
            gap: 8px; /* Reducir espacio entre enlaces */
            margin-top: 10px; /* Reducir espacio superior */
        }

        .menu-link {
            font-size: 14px; /* Reducir tamaño de la fuente */
            padding: 8px 10px; /* Reducir padding */
            color: #555555;
            border-radius: 6px;
            text-align: left;
            transition: color 0.3s ease-in-out, background-color 0.3s ease-in-out;
        }

        .menu-link:hover {
            background-color: #f4f4f4; /* Fondo gris claro al pasar el mouse */
            color: #007bff; /* Azul */
        }

        .menu-link-active {
            font-size: 14px;
            color: #007bff;
            border-right: none;
            padding: 8px 10px;
            position: relative;
            font-weight: bold;

            /* Ajuste preciso de la barra azul */
            &::after {
                content: '';
                position: absolute;
                top: 0;
                right: -18px; /* Ajusta este valor según el ancho del menú y la separación */
                width: 4px; /* Ancho de la barra */
                height: 100%; /* Altura de toda la opción */
                background-color: #007bff; /* Azul */
            }
        }

        .menu .profile-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px; /* Reducir espacio entre logo y texto */
            margin-bottom: 10px; /* Reducir margen inferior */
            margin-top: 40px; /* Reducir margen superior */
        }

        .menu-logo {
            width: 40px; /* Reducir tamaño del logo */
            height: 40px;
            border-radius: 50%;
        }

        .btn-logout {
            width: 90%; /* Ajustar tamaño del botón al nuevo ancho */
            background-color: #ff4c4c;
            color: white;
            padding: 8px; /* Reducir tamaño del botón */
            font-size: 12px; /* Reducir tamaño de la fuente */
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 15px; /* Reducir margen inferior */
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .linea-separadora {
            width: 100%; /* Que ocupe todo el ancho del contenedor */
            height: 1px; /* Grosor de la línea */
            background-color: #e0e0e0; /* Color gris claro */
            margin: 10px 0; /* Espaciado superior e inferior */
            border: none; /* Sin bordes adicionales */
        }

        .btn-logout:hover {
            background-color: #e63939; /* Rojo más oscuro */
        }

        .btn-filter {
            background-color: #007bff; /* Color azul */
            color: white; /* Texto blanco */
            padding: 8px 16px; /* Espaciado interno */
            font-size: 12px; /* Tamaño de fuente */
            border: none; /* Sin borde */
            border-radius: 6px; /* Bordes redondeados */
            cursor: pointer; /* Manita al pasar el mouse */
            margin-left: 10px; /* Espaciado entre el botón y los selectores */
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Sombra ligera */
            font-family: 'Poppins', Arial, sans-serif;
        }

        .btn-filter:hover {
            background-color: #0056b3; /* Azul más oscuro al pasar el mouse */
        }

        .dash-body {
            flex-grow: 1;
            padding: 20px;
        }

        .dash-body h2 {
            font-size: 17px;
            color: #000;
            margin-top: 0px; /* Reducir espacio superior */
            margin-bottom: 20px; /* Espaciado inferior */
            text-align: left;
            line-height: 1; /* Ajustar altura de línea */
        }

        .filter-container {
            display: flex;
            align-items: center; /* Alinea verticalmente los elementos */
            gap: 10px; /* Espaciado entre los elementos */
            margin-bottom: 10px;
        }

        .filter-container select {
            padding: 8px;
            font-size: 12px; /* Reducir tamaño de la fuente */
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .filter-container label {
            font-size: 12px; /* Reducir tamaño de la fuente */
        color: #00b8d9;
        font-weight: bold;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Tres columnas por fila */
            row-gap: 10px; /* Reducir espacio entre filas */
            column-gap: 20px; /* Espacio entre columnas */
            padding: 0px; /* Espaciado interno */
        }

        .stat-box {
            background: #ffffff;
            border: 1px solid #eaeaea;
            border-radius: 10px;
            padding: 15px; /* Espaciado interno más reducido */
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%; /* Asegura que cada tarjeta ocupe su columna */
            max-width: 260px; /* Limitar el ancho máximo */
            margin: 0 auto; /* Centrar los gráficos */
        }

        .stat-box h3 {
            font-size: 14px; /* Reducir tamaño del título */
            font-weight: bold;
            color: #007bff;
            margin-bottom: 8px; /* Espaciado inferior del título */
        }

        .stat-box p {
            font-size: 40px; /* Reducir tamaño del número */
            color: #000;
            font-weight: bold;
            margin: 0;
        }

        .stat-box canvas {
            max-width: 100%; /* Asegura que los gráficos no excedan el ancho */
            height: auto;
        }

        @media (max-width: 1024px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr); /* Cambiar a 2 columnas en pantallas medianas */
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr; /* Cambiar a 1 columna en pantallas pequeñas */
            }
        }

        .btn-logout {
            width: 100%;
            background-color: #ff6b6b; /* Rojo más claro */
            color: white;
            padding: 12px; /* Tamaño del botón */
            font-size: 14px;
            border: none; /* Sin bordes */
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Sombra suave */
            transition: background-color 0.3s ease;
            font-family: 'Poppins', Arial, sans-serif;
            font-weight: bold;
        }

        .btn-logout:hover {
            background-color: #d9534f;
        }
    </style>
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

                <!-- Botón de filtrar -->
                <button id="filterButton" class="btn-filter">Filtrar</button>
            </div>

            <div class="stats-container">
                <div class="stat-box">
                    <h3>Total de citas</h3>
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

            // Poblar los años (por ejemplo, de 2020 a 2030)
            const currentYear = new Date().getFullYear();
            for (let year = currentYear - 5; year <= currentYear + 5; year++) {
                const option = document.createElement("option");
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            }

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
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            actualizarGraficos(data);
                        } else {
                            alert("No se encontraron datos para los filtros seleccionados.");
                        }
                    })
                    .catch(error => console.error("Error al filtrar los datos:", error));
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