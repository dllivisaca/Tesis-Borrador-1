<?php

    //learn from w3schools.com

    session_start();

    if(isset($_SESSION["usuario"])){
        if(($_SESSION["usuario"])=="" or $_SESSION['usuario_rol']!='pac'){
            header("location: ../login.php");
        }else {
            $usuario=$_SESSION["usuario"];
        }

    }else{
        header("location: ../login.php");
    }
    

    //import database
    include("../conexion_db.php");
    $userrow = $database->query("select * from paciente where pacusuario='$usuario'");
    $userfetch=$userrow->fetch_assoc();
    $userid= $userfetch["pacid"];
    $username=$userfetch["pacnombre"];


    if($_POST){
        if(isset($_POST["booknow"])){
            $citanum=$_POST["citanum"];
            $horarioid=$_POST["horarioid"];
            $date=$_POST["date"];
            $horarioid=$_POST["horarioid"];
            $sql2="insert into citas(pacid,citanum,horarioid,citafecha) values ($userid,$citanum,$horarioid,'$date')";
            $result= $database->query($sql2);
            //echo $apponom;
            header("location: citas.php?action=booking-added&id=".$citanum."&titleget=none");

        }
    }
 ?>