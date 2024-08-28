<?php


    session_start();

    if(isset($_SESSION["usuario"])){
        if(($_SESSION["usuario"])=="" or $_SESSION['usuario_rol']!='adm'){
            header("location: ../login.php");
        }
    }else{
        header("location: ../login.php");
    }


         
    if($_POST){
        //import database
        include("../conexion_db.php");
        
        $docid=$_POST["docid"];
        $dia_semana=$_POST["dia_semana"];
        $horainicioman=$_POST["horainicioman"];
        $horafinman=$_POST["horafinman"];
        $horainiciotar=$_POST["horainiciotar"];
        $horafintar=$_POST["horafintar"];
        $sql="insert into disponibilidad_doctor (docid,dia_semana,horainicioman,horafinman,horainiciotar,horafintar) values ($docid,'$dia_semana','$horainicioman','$horafinman','$horainiciotar','$horafintar');";
        $result= $database->query($sql);
        header("location: calendario.php?action=session-added");
        
    }


?>

