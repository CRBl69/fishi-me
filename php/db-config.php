<?php

// Database config

function connect(){
    $_CONFIG_SERVER = "nothing here sorry";
    $_CONFIG_USER = "main";
    $_CONFIG_PW = "i did not forget to remove this *wink*";
    $_CONFIG_DB = "social";

    $connection = new mysqli($_CONFIG_SERVER, $_CONFIG_USER, $_CONFIG_PW, $_CONFIG_DB);

    if($connection->connect_error){
        die("Connection failed: " . $connection->connect_error);
    }

    return $connection;
}
