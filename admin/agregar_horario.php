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
        $titulo=$_POST["titulo"];
        $docid=$_POST["docid"];
        $fecha=$_POST["fecha"];
        $hora=$_POST["hora"];
        $sql="insert into horarios (docid,titulo,horariofecha,horariohora) values ($docid,'$titulo','$fecha','$hora');";
        $result= $database->query($sql);
        header("location: horarios.php?action=session-added&title=$titulo");
        
    }


?>