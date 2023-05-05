<?php

require "../Core/AutoLoader.php";
require "../Core/Entity.php";

use Core\Entity;

function scan($path, $function){
    foreach(scandir($path) as $file){
        if(str_starts_with($file, "."))continue;
        else if(is_dir($path . "/" . $file))scan($path . "/" . $file, $function);
        else $function($path . "/" . $file);
    }
}

$sqlFiles = [];

function replace($slqName, $arr){
    global $sqlFiles;
    
    preg_match_all("/{(.[^}]*)}/", $sqlFiles[$slqName], $groups);

    $req = $sqlFiles[$slqName];
    foreach($groups[1] as $group){
        $req = str_replace($group, $arr[$group], $req);
    }
    $req = preg_replace("/[{}]/", "", $req);
    $req = preg_replace("/\n/", " ", $req);  
    while(str_contains($req, "  ")){
        $req = preg_replace("/  /", " ", $req);    
    }

    return $req;
}

scan(
    __DIR__ . "/sql",
    function($path){
        global $sqlFiles;

        $fileName = explode("/", $path);
        $fileName = array_pop($fileName);
        $fileName = str_replace(".sql", "", $fileName);

        $file = fopen($path, "r");
        $sqlFiles[$fileName] = fread($file, filesize($path));
        fclose($file);
    }
);

$migrationSql = "";
$entitysName = [];

scan(
    __DIR__ . "/../Entity",
    function($path){
        include_once $path;

        global $migrationSql;
        global $entitysName;
        
        $entityName = explode("/", $path);
        $entityName = array_pop($entityName);
        $entityName = str_replace(".php", "", $entityName);
        $entityName = strtolower($entityName);

        array_push($entitysName, $entityName);

        $class = str_replace(__DIR__ . "/../", "", $path);
        $class = str_replace(".php", "", $class);
        $class = str_replace("/", "\\", $class);

        $propsName = [];

        try{
            $result = Entity::getDb()->prepare(replace("tableExist", ["tableName" => $entityName]));
            $result->execute();
            $result->fetch();
            $exist = true;
        }
        catch(\PDOException $th){
            $exist = false;
        }

        if($exist === false){
            $rp = new \ReflectionObject(new $class());
            foreach($rp->getProperties(\ReflectionProperty::IS_PRIVATE) as $prop){
                if(str_starts_with($prop->getType()->getName(), "Entity\\")){
                    $propEntity = explode("\\", $prop->getType()->getName());
                    $propEntity = array_pop($propEntity);

                    array_push($propsName, $prop->getName() . "_id INT");
                }
                else if($prop->getDocComment() !== false){
                    preg_match_all("/@([a-z]*){(.*)}/", $prop->getDocComment(), $groups);
                    if(
                        isset($groups) &&
                        isset($groups[1]) &&
                        isset($groups[1][0]) && 
                        $groups[1][0] === "type"
                    ){
                        array_push($propsName, $prop->getName() . " " . $groups[2][0]);
                    }
                }
                else if($prop->getType()->getName() !== "array"){
                    die("The props '{$prop->getName()}' of Entity '{$entityName}' ah not @type.");
                }
            }

            $migrationSql .= replace(
                "createTable", 
                [
                    "tableName" => $entityName,
                    "tableProps" => implode(
                        ", ",
                        $propsName,
                    ),
                ]
            ) . "\n";
        }
        else {
            $rp = new \ReflectionObject(new $class());
            foreach($rp->getProperties(\ReflectionProperty::IS_PRIVATE) as $prop){

                $columnName = strToLower($prop->getName());
                $columnType = "";

                if($prop->getType()->getName() === "array") continue;
                else if(str_starts_with($prop->getType()->getName(), "Entity\\")){
                    $propEntity = explode("\\", $prop->getType()->getName());
                    $propEntity = array_pop($propEntity);

                    $columnName .= "_id";
                    $columnType = "INT";
                }
                else if($prop->getDocComment() !== false){
                    preg_match_all("/@([a-z]*){(.*)}/", $prop->getDocComment(), $groups);
                    if(
                        isset($groups) &&
                        isset($groups[1]) &&
                        isset($groups[1][0]) && 
                        $groups[1][0] === "type"
                    )$columnType = $groups[2][0];
                }

                if(isset($columnType) === false) 
                    die("The props '{$prop->getName()}' of Entity '{$entityName}' ah not @type.");
                
                $result = Entity::getDb()
                ->prepare(
                    replace(
                        "columnExist", 
                        [
                            "tableName" => $entityName,
                            "columnName" => $columnName
                        ]
                    )
                ); 
                $result->execute();
                $result = $result->fetchAll(\PDO::FETCH_ASSOC);
            
                if(isset($result[0]) === false){
                    $migrationSql .= replace(
                        "createColumn", 
                        [
                            "tableName" => $entityName,
                            "columnName" => $columnName,
                            "columnType" => $columnType
                        ]
                    ) . "\n";
                }
                else if(strToLower($result[0]["data_type"]) !== strToLower($columnType)){
                    $migrationSql .= replace(
                        "alterColumn", 
                        [
                            "tableName" => $entityName,
                            "columnName" => $columnName,
                            "columnType" => $columnType
                        ]
                    ) . "\n";
                }

                array_push($propsName, $columnName);
            }

            $result = Entity::getDb()
            ->prepare(
                replace(
                    "showColumns", 
                    [
                        "tableName" => $entityName
                    ]
                )
            ); 
            $result->execute();
            $result = $result->fetchAll(\PDO::FETCH_ASSOC);

            foreach($result as $columnName){
                $columnName = $columnName["column_name"];
                if(in_array($columnName, $propsName) === false){
                    $migrationSql .= replace(
                        "dropColumn", 
                        [
                            "tableName" => $entityName,
                            "columnName" => $columnName
                        ]
                    ) . "\n";
                }
            }
        }
        
    }
);

$result = Entity::getDb()
->prepare(
    replace(
        "showTables", 
        []
    )
); 
$result->execute();
$result = $result->fetchAll(\PDO::FETCH_ASSOC);
foreach($result as $tableName){
    $tableName = $tableName["table_name"];

    if(in_array($tableName, $entitysName) === false){
        $migrationSql .= replace(
            "dropTable", 
            [
                "tableName" => $tableName,
            ]
        ) . "\n";
    }
}

$migrationFile = fopen("../migration.sql", "w");
fwrite($migrationFile, $migrationSql);
fclose($migrationFile);