<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/horarios.css">
        
    <title>Horarios</title>
    
</head>
<body>
    <?php
    error_reporting(E_ERROR | E_PARSE);

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
    ?>
    <div class="container">
        <div class="menu">
            <div class="profile-container">
                <img src="../img/logo.png" alt="Logo" class="menu-logo">
                <p class="profile-title">Administrador</p>
            </div>
            <a href="../logout.php"><button class="btn-logout">Cerrar sesión</button></a>
            <div class="linea-separadora"></div>
            <div class="menu-links">
                <a href="dashboard.php" class="menu-link">Dashboard</a>
                <a href="doctores.php" class="menu-link">Doctores</a>
                <a href="pacientes.php" class="menu-link">Pacientes</a>
                <a href="horarios.php" class="menu-link menu-link-active">Horarios disponibles</a>
                <a href="citas.php" class="menu-link">Citas agendadas</a>
                <a href="opiniones_recibidas.php" class="menu-link">Opiniones recibidas</a>
            </div>
        </div>

        <div class="dash-body">
            <div class="header-actions">
            <!-- Sección izquierda: Botón Atrás y barra de búsqueda -->
            <div class="header-inline">
                <a href="horarios.php">
                    <button class="btn-action">← Atrás</button>
                </a>
                <p class="heading-main12" style="margin: 0; font-size: 17px; color: rgb(49, 49, 49); align-self: left;">
                Gestor de horarios
                </p>
            </div>
        </div>
        <div class="filter-row">
            <form method="POST" action="horarios.php">
                <label for="docid">Doctor:</label>
                <select name="docid" id="docid" class="box filter-container-items">
                    <option value="" disabled selected hidden>Escoge un doctor de la lista</option>
                    <?php 
                        $list11 = $database->query("select * from doctor order by docnombre asc;");
                        while ($row = $list11->fetch_assoc()) {
                            echo "<option value='".$row["docid"]."'>".$row["docnombre"]."</option>";
                        }
                    ?>
                </select>
                <button type="submit" class="btn-primary-soft btn button-icon btn-filter">Buscar</button>
            </form>
        </div>

                
                
                <?php
                // Definir la función globalmente para que esté disponible en cualquier contexto
                function ordenarDiasSemana($a, $b) {
                    $ordenDias = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"];
                    $posA = array_search($a['dia_semana'], $ordenDias);
                    $posB = array_search($b['dia_semana'], $ordenDias);
                    return $posA - $posB;
                }

                    if($_POST){
                        //print_r($_POST);                    

                        $sqlpt2="";
                        if(!empty($_POST["docid"])){
                            $docid=$_POST["docid"];
                            $sqlpt2=" doctor.docid=$docid ";
                        }

                        
                                            
                        // Consulta SQL para obtener los horarios disponibles de la tabla `disponibilidad_doctor`
                        $sqlmain = "SELECT doctor.docid, doctor.docnombre, doctor.especialidades, disponibilidad_doctor.dia_semana, 
                        disponibilidad_doctor.horainicioman, disponibilidad_doctor.horafinman, 
                        disponibilidad_doctor.horainiciotar, disponibilidad_doctor.horafintar 
                        FROM doctor 
                        LEFT JOIN disponibilidad_doctor ON doctor.docid = disponibilidad_doctor.docid";

                        // Aplica el filtro por doctor si se selecciona uno
                        if (!empty($sqlpt2)) {
                        $sqlmain .= " WHERE $sqlpt2";
                        }

                    } else {
                        // Consulta por defecto si no se ha seleccionado ningún filtro
                        $sqlmain = "SELECT doctor.docid, doctor.docnombre, doctor.especialidades, disponibilidad_doctor.dia_semana, disponibilidad_doctor.horainicioman, disponibilidad_doctor.horafinman, disponibilidad_doctor.horainiciotar, disponibilidad_doctor.horafintar 
                        FROM doctor 
                        LEFT JOIN disponibilidad_doctor ON doctor.docid = disponibilidad_doctor.docid";
                    }

                        
                        //echo $sqlmain;

                        
                        
                        //
                    


                ?>
                  
                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                                <th class="table-headin">
                                    
                                
                                 Nombre del doctor
                                
                                </th>
                                
                                <th class="table-headin">
                                    Especialidad
                                </th>
                                <th class="table-headin">
                                    
                                    Horario disponible
                                    
                                </th>
                                
                                
                                <th class="table-headin">
                                    
                                    Acciones
                                    
                                </tr>
                        </thead>
                        <tbody>
                        
                            <?php

                                // Ejecutar la consulta
                                $result = $database->query($sqlmain);
                               
                                if ($result->num_rows == 0) {
                                    echo '<tr><td colspan="4">
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No se encontraron horarios disponibles!</p>
                                    <a class="non-style-link" href="horarios.php"><button class="login-btn btn-primary-soft btn" style="margin-left:20px;">&nbsp; Ver todos los horarios &nbsp;</button></a>
                                    </center></td></tr>';
                                } else {
                                    // Agrupar los resultados por doctor
                                    $doctores = [];
                                    while ($row = $result->fetch_assoc()) {
                                        $docid = $row['docid'];
                                        $docnombre = $row['docnombre'];
                                        $espe = $row['especialidades'];
                                
                                        // Obtener la especialidad del doctor
                                        $especial_res = $database->query("SELECT espnombre FROM especialidades WHERE id='$espe'");
                                        $especial_array = $especial_res->fetch_assoc();
                                        $especial_name = $especial_array['espnombre'] ?? 'N/A';
                                
                                        // Inicializar el array si el doctor no está ya registrado
                                        if (!isset($doctores[$docid])) {
                                            $doctores[$docid] = [
                                                'docnombre' => $docnombre,
                                                'especialidad' => $especial_name,
                                                'horarios' => []
                                            ];
                                        }
                                
                                        // Variable para mostrar horarios válidos
                                        $horarios_mostrar = '';

                                        // Verificar que ambos valores (hora de inicio y fin) no sean "00:00:00" para el turno de la mañana
                                        if (!empty($row['horainicioman']) && !empty($row['horafinman']) && $row['horainicioman'] !== "00:00:00" && $row['horafinman'] !== "00:00:00") {
                                            $horarios_mostrar .= substr($row['horainicioman'], 0, 5) . ' - ' . substr($row['horafinman'], 0, 5) . '<br>';
                                        }

                                        // Verificar que ambos valores (hora de inicio y fin) no sean "00:00:00" para el turno de la tarde
                                        if (!empty($row['horainiciotar']) && !empty($row['horafintar']) && $row['horainiciotar'] !== "00:00:00" && $row['horafintar'] !== "00:00:00") {
                                            $horarios_mostrar .= substr($row['horainiciotar'], 0, 5) . ' - ' . substr($row['horafintar'], 0, 5);
                                        }

                                        // Si no hay horarios válidos, saltar a la siguiente iteración
                                        if (empty($horarios_mostrar)) {
                                            continue;
                                        }

                                        // Añadir los horarios válidos al array de doctores
                                        $doctores[$docid]['horarios'][] = [
                                            'dia_semana' => $row['dia_semana'] ?? 'N/A',
                                            'horario' => $horarios_mostrar
                                        ];
                                    }
                                
                                    
                            // Mostrar los resultados por cada doctor
                                    foreach ($doctores as $docid => $doctor) {
                                        echo '<tr>';
                                        echo '<td>' . $doctor['docnombre'] . '</td>';
                                        echo '<td>' . $doctor['especialidad'] . '</td>';
                                        echo '<td style="text-align:center;">';

                                        if (empty($doctor['horarios'])) {
                                            echo 'No se encontraron horarios disponibles';
                                        } else {
                                            // Mostrar los horarios en dos columnas
                                            echo '<div style="display: flex; justify-content: space-around;">';
                                            for ($i = 0; $i < count($doctor['horarios']); $i += 2) {
                                                echo '<div style="width: 45%;">';
                                                // Mostrar el día y los horarios de la primera columna
                                                echo '<b>' . $doctor['horarios'][$i]['dia_semana'] . '</b><br>';
                                                echo $doctor['horarios'][$i]['horario'] . '<br>';

                                                // Verificar si existe una segunda columna
                                                if (isset($doctor['horarios'][$i + 1])) {
                                                    echo '<b>' . $doctor['horarios'][$i + 1]['dia_semana'] . '</b><br>';
                                                    echo $doctor['horarios'][$i + 1]['horario'] . '<br>';
                                                }
                                                echo '</div>';
                                            }
                                            echo '</div>';
                                        }
                                        echo '</td>';

                                        // Mostrar el botón de "Agregar horario" si no tiene horarios
                                        echo '<td>';
                                        if (empty($doctor['horarios'])) {
                                            echo '<a href="agghorario_fijo.php?id=' . $docid . '" class="non-style-link">
                                                    <button class="btn-primary-soft btn button-icon btn-add">Agregar horario</button>
                                                </a>';
                                        } else {
                                            // Mostrar los botones "Editar" y "Eliminar" si tiene horarios
                                            echo '<a href="editar_horario.php?id=' . $docid . '" class="non-style-link">
                                                    <button class="btn-primary-soft btn button-icon btn-view">Editar</button>
                                                </a>';
                                           

                                            echo '<a href="borrar_horario.php?id=' . $docid . '" class="non-style-link" onclick="return confirm(\'¿Estás segura de eliminar todos los horarios guardados del doctor?\');">
                                                <button class="btn-primary-soft btn button-icon btn-delete">Eliminar</button>
                                            </a>';
                                        }
                                        echo '</td>';
                                        echo '</tr>';
                                    


                                        // Aquí es donde nos aseguramos de que el ID del doctor sea único para cada enlace de edición o eliminación
                                        /* echo '<td>';
                                        echo '<a href="editar_horario.php?id=' . $docid . '" class="non-style-link">
                                                <button class="btn-primary-soft btn button-icon btn-view">Editar</button>
                                            </a>';
                                        echo '<a href="delete_horario.php?id=' . $docid . '" class="non-style-link">
                                                <button class="btn-primary-soft btn button-icon btn-delete">Eliminar</button>
                                            </a>';
                                        echo '</td>';
                                        echo '</tr>'; */
                                    }
                                }
                            ?>
 
                            </tbody>

                        </table>
                        </div>
                        </center>
                   </td> 
                </tr>
                       
                        
                        
            </table>
        </div>
    </div>
    <?php
    
    if($_GET){
        $id=$_GET["id"];
        $action=$_GET["action"];
        if($action=='agregar_horario'){
            $sqlmain= "select * from doctor where docid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $docnombre=$row["docnombre"];
            
            $espe=$row["especialidades"];
            
            $especial_res= $database->query("select espnombre from especialidades where id='$espe'");
            $especial_array= $especial_res->fetch_assoc();
            $especial_name=$especial_array["espnombre"];
            


            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2></h2>
                        <a class="close" href="horarios.php">&times;</a>
                        <div class="content">
                            eDoc Web App<br>
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                        
                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">View Details.</p><br><br>
                                </td>
                            </tr>
                            
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Name: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.$docnombre.'<br><br>
                                </td>
                                
                            </tr>
                            
                           
                            
                           
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="espec" class="form-label">Especialidades: </label>
                                    
                                </td>
                            </tr>
                            <tr>
                            <td class="label-td" colspan="2">
                            '.$especial_name.'<br><br>
                            </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="horarios.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                
                                    
                                </td>
                
                            </tr>
                           

                        </table>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>
            ';
        }elseif($action=='session-added'){
            $titleget=$_GET["title"];
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                    <br><br>
                        <h2>Session Placed.</h2>
                        <a class="close" href="horarios.php">&times;</a>
                        <div class="content">
                        '.substr($titleget,0,40).' was scheduled.<br><br>
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        
                        <a href="horarios.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
                        <br><br><br><br>
                        </div>
                    </center>
            </div>
            </div>
            ';
        }elseif($action=='view'){
            $sqlmain= "select horarios.horarioid,horarios.titulo,doctor.docnombre,horarios.horariofecha,horarios.horariohora from horarios inner join doctor on horarios.docid=doctor.docid  where  horarios.horarioid=$id";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $docnombre=$row["docnombre"];
            $horarioid=$row["horarioid"];
            $titulo=$row["titulo"];
            $horariofecha=$row["horariofecha"];
            $horariohora=$row["horariohora"];
            
           
            
            $sqlmain12= "select * from citas inner join paciente on paciente.pacid=citas.pacid inner join horarios on horarios.horarioid=citas.horarioid where horarios.horarioid=$id;";
            $result12= $database->query($sqlmain12);
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup" style="width: 70%;">
                    <center>
                        <h2></h2>
                        <a class="close" href="horarios.php">&times;</a>
                        <div class="content">
                            
                            
                        </div>
                        <div class="abc scroll" style="display: flex;justify-content: center;">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                        
                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">View Details.</p><br><br>
                                </td>
                            </tr>
                            
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Título de la: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.$titulo.'<br><br>
                                </td>
                                
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Email" class="form-label">Doctor of this session: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$docnombre.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="ci" class="form-label">Scheduled Date: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$horariofecha.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Tele" class="form-label">Scheduled Time: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$horariohora.'<br><br>
                                </td>
                            </tr>
                                                        
                            <tr>
                            <td colspan="4">
                                <center>
                                 <div class="abc scroll">
                                 <table width="100%" class="sub-table scrolldown" border="0">
                                 <thead>
                                 <tr>   
                                        <th class="table-headin">
                                             Patient ID
                                         </th>
                                         <th class="table-headin">
                                             Patient name
                                         </th>
                                         <th class="table-headin">
                                             
                                             Appointment number
                                             
                                         </th>
                                        
                                         
                                         <th class="table-headin">
                                             Patient Telephone
                                         </th>
                                         
                                 </thead>
                                 <tbody>';
                                 
                
                
                                         
                                         $result= $database->query($sqlmain12);
                
                                         if($result->num_rows==0){
                                             echo '<tr>
                                             <td colspan="7">
                                             <br><br><br><br>
                                             <center>
                                             <img src="../img/notfound.svg" width="25%">
                                             
                                             <br>
                                             <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We  couldnt find anything related to your keywords !</p>
                                             <a class="non-style-link" href="citas.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Appointments &nbsp;</font></button>
                                             </a>
                                             </center>
                                             <br><br><br><br>
                                             </td>
                                             </tr>';
                                             
                                         }
                                         else{
                                         for ( $x=0; $x<$result->num_rows;$x++){
                                             $row=$result->fetch_assoc();
                                             $citanum=$row["citanum"];
                                             $pacid=$row["pacid"];
                                             $pnombre=$row["pnombre"];
                                             $ptel=$row["ptel"];
                                             
                                             echo '<tr style="text-align:center;">
                                                <td>
                                                '.substr($pacid,0,15).'
                                                </td>
                                                 <td style="font-weight:600;padding:25px">'.
                                                 
                                                 substr($pnombre,0,25)
                                                 .'</td >
                                                 <td style="text-align:center;font-size:23px;font-weight:500; color: var(--btnnicetext);">
                                                 '.$citanum.'
                                                 
                                                 </td>
                                                 <td>
                                                 '.substr($ptel,0,25).'
                                                 </td>
                                                 
                                                 
                
                                                 
                                             </tr>';
                                             
                                         }
                                     }
                                          
                                     
                
                                    echo '</tbody>
                
                                 </table>
                                 </div>
                                 </center>
                            </td> 
                         </tr>

                        </table>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>
            ';  
    }
}
        
    ?>
    </div>

</body>
</html>