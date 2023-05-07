<?php

spl_autoload_register(function(string $class){
    $class = str_replace("\\", "/", $class);
    $class .= ".php";

    if(file_exists($class) === true){
        include $class;
    }
});