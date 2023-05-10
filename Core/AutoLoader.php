<?php

spl_autoload_register(function(string $class){
    $class = __DIR__ . "/../" . str_replace("\\", "/", $class);
    $class .= ".php";

    if(file_exists($class) === true){
        include $class;
    }
});