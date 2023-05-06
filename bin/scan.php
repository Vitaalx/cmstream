<?php

function scan($path, $function){
    foreach(scandir($path) as $file){
        if(str_starts_with($file, "."))continue;
        else if(is_dir($path . "/" . $file))scan($path . "/" . $file, $function);
        else $function($path . "/" . $file);
    }
}