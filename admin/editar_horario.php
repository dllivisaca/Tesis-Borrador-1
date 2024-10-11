<?php
// Iniciar la sesión
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] == "" || $_SESSION['usuario_rol'] != 'adm') {
    header("Location: ../login.php");
    exit;
}

include("../conexion_db.php");

if ($_GET) {
    $docid = $_GET['id'];

    // Consultar los datos del doctor y su disponibilidad actual
    $sql = "SELECT doctor.docnombre, doctor.especialidades, disponibilidad_doctor.* 
            FROM doctor 
            LEFT JOIN disponibilidad_doctor 
            ON doctor.docid = disponibilidad_doctor.docid 
            WHERE doctor.docid = '$docid'";

    $result = $database->query($sql);
    $doctor = $result->fetch_assoc();

    // Obtener especialidad del doctor
    $especialidad_res = $database->query("SELECT espnombre FROM especialidades WHERE id = '{$doctor['especialidades']}'");
    $especialidad = $especialidad_res->fetch_assoc()['espnombre'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Horario</title>
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
    <div class="container">
        <h2>Editar Horario del Doctor</h2>
        <form action="update_horario.php" method="POST">
            <input type="hidden" name="docid" value="<?php echo $docid; ?>">

            <label for="docnombre">Nombre del Doctor:</label>
            <input type="text" id="docnombre" name="docnombre" value="<?php echo $doctor['docnombre']; ?>" readonly>

            <label for="especialidad">Especialidad:</label>
            <input type="text" id="especialidad" name="especialidad" value="<?php echo $especialidad; ?>" readonly>

            <label for="dia_semana">Día de la semana:</label>
            <select name="dia_semana" id="dia_semana">
                <option value="Lunes" <?php if ($doctor['dia_semana'] == "Lunes") echo "selected"; ?>>Lunes</option>
                <option value="Martes" <?php if ($doctor['dia_semana'] == "Martes") echo "selected"; ?>>Martes</option>
                <option value="Miércoles" <?php if ($doctor['dia_semana'] == "Miércoles") echo "selected"; ?>>Miércoles</option>
                <option value="Jueves" <?php if ($doctor['dia_semana'] == "Jueves") echo "selected"; ?>>Jueves</option>
                <option value="Viernes" <?php if ($doctor['dia_semana'] == "Viernes") echo "selected"; ?>>Viernes</option>
                <option value="Sábado" <?php if ($doctor['dia_semana'] == "Sábado") echo "selected"; ?>>Sábado</option>
                <option value="Domingo" <?php if ($doctor['dia_semana'] == "Domingo") echo "selected"; ?>>Domingo</option>
            </select>

            <label for="horainicioman">Hora Inicio Mañana:</label>
            <input type="time" id="horainicioman" name="horainicioman" value="<?php echo substr($doctor['horainicioman'], 0, 5); ?>">

            <label for="horafinman">Hora Fin Mañana:</label>
            <input type="time" id="horafinman" name="horafinman" value="<?php echo substr($doctor['horafinman'], 0, 5); ?>">

            <label for="horainiciotar">Hora Inicio Tarde:</label>
            <input type="time" id="horainiciotar" name="horainiciotar" value="<?php echo substr($doctor['horainiciotar'], 0, 5); ?>">

            <label for="horafintar">Hora Fin Tarde:</label>
            <input type="time" id="horafintar" name="horafintar" value="<?php echo substr($doctor['horafintar'], 0, 5); ?>">

            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>
