<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Doctor</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
</style>
</head>
<body>
    <?php

    //learn from w3schools.com

    session_start();

    if(isset($_SESSION["usuario"])){
        if(($_SESSION["usuario"])=="" or $_SESSION['usuario_rol']!='adm'){
            header("location: ../login.php");
        }

    }else{
        header("location: ../login.php");
    }
    
    

    //import database
    include("../conexion_db.php");



    if($_POST){
        //print_r($_POST);
        $result= $database->query("select * from usuarios");
        $name=$_POST['name'];
        $ci=$_POST['ci'];
        $espec=$_POST['espec'];
        $usuario=$_POST['usuario'];
        $telf=$_POST['Telf'];
        $password=$_POST['password'];
        $cpassword=$_POST['cpassword'];
        
        if ($password==$cpassword){
            $error='3';
            $result= $database->query("select * from usuarios where usuario='$usuario';");
            if($result->num_rows==1){
                $error='1';
            }else{

                $sql1="insert into doctor(docusuario,docnombre,docpassword,docci,doctelf,especialidades) values('$usuario','$name','$password','$ci','$telf',$espec);";
                $sql2="insert into usuarios values('$usuario','doc','$ci')";
                $database->query($sql1);
                $database->query($sql2);

                //echo $sql1;
                //echo $sql2;
                $error= '4';
                
            }
            
        }else{
            $error='2';
        }
    
    
        
        
    }else{
        //header('location: signup.php');
        $error='3';
    }
    

    header("location: doctores.php?action=add&error=".$error);
    ?>
    
   

</body>
</html>