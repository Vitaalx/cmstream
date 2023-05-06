<?php

require __DIR__ . "/../Core/AutoLoader.php";
require __DIR__ . "/scan.php";

use Core\Entity;

$path = __DIR__ . "/../migration.sql";
$file = fopen($path, "r");
$lines = fread($file, filesize($path));
$lines = explode("\n", $lines);

foreach($lines as $line){
    if(!$line)continue;
    $result = Entity::getDb()->query($line);
}

fclose($file);