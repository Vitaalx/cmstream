<?php

namespace doMigration;

require_once __DIR__ . "/../Core/AutoLoader.php";
require_once __DIR__ . "/scan.php";
require_once __DIR__ . "/../config.php";

use Core\Entity;

$path = __DIR__ . "/../migration.sql";

if(filesize($path) !== 0){
    $file = fopen($path, "r");
    $lines = fread($file, filesize($path));
    $lines = explode("\n", $lines);

    foreach($lines as $line){
        if(!$line)continue;
        $result = Entity::getDb()->query($line);
    }

    fclose($file);
}