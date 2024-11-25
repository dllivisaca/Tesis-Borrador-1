<?php

    session_start();

    if(isset($_SESSION["usuario"])){
        if(($_SESSION["usuario"])=="" or $_SESSION['usuario_rol']!='adm'){
            header("location: ../login.php");
        }

    }else{
        header("location: ../login.php");
    }
    
    
    if($_GET){
        //import database
        include("../conexion_db.php");
        $id=$_GET["id"];
        $result001= $database->query("select * from paciente where pacid=$id;");
        $usuario=($result001->fetch_assoc())["pacusuario"];
        $sql= $database->query("delete from usuarios where usuario='$usuario';");
        $sql= $database->query("delete from paciente where pacusuario='$usuario';");
        //print_r($email);
        header("location: pacientes.php");
    }


?>