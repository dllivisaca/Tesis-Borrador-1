<?php
session_start();

if(isset($_SESSION["usuario"])){
    if(($_SESSION["usuario"])=="" or $_SESSION['usuario_rol']!='adm'){
        header("location: ../login.php");
        exit();
    }
}else{
    header("location: ../login.php");
    exit();
}

include("../conexion_db.php");

if(isset($_GET['id'])){
    $docid = $_GET['id'];

    // Eliminar todos los horarios del doctor
    $sql_delete = "DELETE FROM disponibilidad_doctor WHERE docid = '$docid'";
    if($database->query($sql_delete)){
        // Redirigir con un mensaje de éxito
        echo "<script>
                alert('Todos los horarios del doctor han sido eliminados correctamente.');
                window.location.href = 'horarios2.php';
              </script>";
    } else {
        // Mostrar mensaje de error
        echo "<script>
                alert('Ocurrió un error al eliminar los horarios.');
                window.location.href = 'horarios2.php';
              </script>";
    }
} else {
    // Si no se proporciona el ID, redirigir
    header("location: horarios2.php");
    exit();
}
?>
