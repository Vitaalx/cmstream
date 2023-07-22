<?php

/**
 * @param string $class
 * @return void
 * 
 * Load the class in function of the namespace.
 * this function is called when a class is instanciated or when a class is called.
 */
spl_autoload_register(function(string $class){
    $class = __DIR__ . "/../" . str_replace("\\", "/", $class);
    $class .= ".php";

    if(file_exists($class) === true){
        include $class;
    }
});