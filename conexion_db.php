<?php

    $database= new mysqli("localhost","root","","borrador1");
    if ($database->connect_error){
        die("Connection failed:  ".$database->connect_error);
    }

