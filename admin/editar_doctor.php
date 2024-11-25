
<?php
    
    

    //import database
    include("../conexion_db.php");



    if($_POST){
        //print_r($_POST);
        $result= $database->query("select * from usuarios");
        $name=$_POST['name'];
        $ci=$_POST['ci'];
        $oldusuario=$_POST["oldusuario"];
        $espec=$_POST['espec'];
        $usuario=$_POST['usuario'];
        $telf=$_POST['Telf'];
        $password=$_POST['password'];
        $cpassword=$_POST['cpassword'];
        $id=$_POST['id00'];
        
        if ($password==$cpassword){
            $error='3';
            $result= $database->query("select doctor.docid from doctor inner join usuarios on doctor.docusuario=usuarios.usuario where usuarios.usuario='$usuario';");
            //$resultqq= $database->query("select * from doctor where docid='$id';");
            if($result->num_rows==1){
                $id2=$result->fetch_assoc()["docid"];
            }else{
                $id2=$id;
            }
            
            echo $id2."jdfjdfdh";
            if($id2!=$id){
                $error='1';
                //$resultqq1= $database->query("select * from doctor where docemail='$email';");
                //$did= $resultqq1->fetch_assoc()["docid"];
                //if($resultqq1->num_rows==1){
                    
            }else{

                //$sql1="insert into doctor(docemail,docname,docpassword,docnic,doctel,especialidades) values('$email','$name','$password','$nic','$tele',$spec);";
                $sql1="update doctor set docusuario='$usuario',docnombre='$name',docpassword='$password',docci='$ci',doctelf='$telf',especialidades=$espec where docid=$id ;";
                $database->query($sql1);
                
                $sql1="update usuarios set usuario='$usuario' where usuario='$oldusuario' ;";
                $database->query($sql1);
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
    

    header("location: doctores.php?action=edit&error=".$error."&id=".$id);
    ?>
    
   

</body>
</html>