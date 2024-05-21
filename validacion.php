<?php

include('conexion_db.php');

$USUARIO=$_POST['usuario'];
$PASSWORD=$_POST['password'];

$consulta = "SELECT*FROM usuarios where usuario = '$USUARIO' and password = '$PASSWORD'";
$resultado = mysqli_query($conexion,$consulta);

$filas=mysqli_fetch_array($resultado);	

if($filas['id_rol'] == 1){ //administrador
    header("location:inicio_admin.html");
}else if($filas['id_rol'] == 2){
    header("location:inicio_doctor.html");
}else if($filas['id_rol'] == 3){
    header("location:inicio_paciente.html");
}else{
   include("index.html"); 
   ?>
   <h1>ERROR DE AUTENTIFICACIÓN</h1>
   <?php
}
mysqli_free_result($resultado);
mysqli_close($conexion);







