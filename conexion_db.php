<?php

    $database= new mysqli("localhost","root","","famysalud");
    if ($database->connect_error){
        die("Connection failed:  ".$database->connect_error);
    }

