<?php

require __DIR__ . "/../Core/AutoLoader.php";
require __DIR__ . "/scan.php";

use Core\Entity;

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

// test
// $result = Entity::getDb()
// ->prepare(
//     replace(
//         "columnExist", 
//         [
//             "tableName" => "_test",
//             "columnName" => "tt",
//         ]
//     )
// ); 
// $result->execute();
// $result = $result->fetchAll(\PDO::FETCH_ASSOC);
// print_r($result);

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

        $prefixedEntityName = "_" . $entityName;

        array_push($entitysName, $prefixedEntityName);

        $class = str_replace(__DIR__ . "/../", "", $path);
        $class = str_replace(".php", "", $class);
        $class = str_replace("/", "\\", $class);

        $propsName = [];

        try{
            $result = Entity::getDb()->prepare(replace("tableExist", ["tableName" => $prefixedEntityName]));
            $result->execute();
            $result->fetch();
            $exist = true;
        }
        catch(\PDOException $th){
            $exist = false;
        }

        if($exist === false){
            //create entity
            $rp = new \ReflectionObject(new $class([]));
            //get all props
            foreach($rp->getProperties(\ReflectionProperty::IS_PRIVATE) as $prop){
                //type is entity
                if(str_starts_with($prop->getType()->getName(), "Entity\\")){
                    preg_match_all("/@([a-z]*){(.*)}/", $prop->getDocComment(), $groups);
                    $type = "";

                    foreach ($groups[1] ?? [] as $key => $value) {
                        if($value === "notnullable"){
                            $type .= "NOT NULL ";
                        }
                        else if($value === "unique"){
                            $type .= "UNIQUE ";
                        }
                    }

                    array_push($propsName, $prop->getName() . "_id INT" . $type);
                }
                //type is defined in comment
                else if($prop->getDocComment() !== false){
                    preg_match_all("/@([a-z]*){(.*)}/", $prop->getDocComment(), $groups);
                    $type = "";
                    //auto define type of prop id
                    if($prop->getName() === "id") $type = "SERIAL PRIMARY KEY";
                    //get parameter and type
                    else foreach ($groups[1] ?? [] as $key => $value) {
                        if($value === "type"){
                            $type .= $groups[2][$key] . " ";
                        }
                        else if($value === "notnullable"){
                            $type .= "NOT NULL ";
                        }
                        else if($value === "unique"){
                            $type .= "UNIQUE ";
                        }
                    }
                    array_push($propsName, $prop->getName() . " " . $type);
                }
                else if($prop->getType()->getName() !== "array"){
                    die("The props '{$prop->getName()}' of Entity '{$entityName}' ah not @type.");
                }
            }

            $migrationSql .= replace(
                "createTable", 
                [
                    "tableName" => $prefixedEntityName,
                    "tableProps" => implode(
                        ", ",
                        $propsName,
                    ),
                ]
            ) . "\n";
        }
        else {
            //edit entity
            $rp = new \ReflectionObject(new $class([]));
            //get all props
            foreach($rp->getProperties(\ReflectionProperty::IS_PRIVATE) as $prop){

                $columnName = strToLower($prop->getName());
                $columnType = "";
                $isNotNullable = false;
                $isUnique = false;

                if($prop->getType()->getName() === "array") continue;
                //type is entity
                else if(str_starts_with($prop->getType()->getName(), "Entity\\")){
                    $columnName .= "_id";
                    $columnType = "INT";

                    preg_match_all("/@([a-z]*){(.*)}/", $prop->getDocComment(), $groups);
                    $type = "";

                    foreach ($groups[1] ?? [] as $key => $value) {
                        if($value === "notnullable"){
                            $isNotNullable = true;
                        }
                        else if($value === "unique"){
                            $isUnique = true;
                        }
                    }

                }
                else if($prop->getDocComment() !== false){
                    preg_match_all("/@([a-z]*){(.*)}/", $prop->getDocComment(), $groups);
                    foreach ($groups[1] ?? [] as $key => $value) {
                        if($value === "type"){
                            $columnType = $groups[2][$key];
                        }
                        else if($value === "notnullable"){
                            $isNotNullable = true;
                        }
                        else if($value === "unique"){
                            $isUnique = true;
                        }
                    }
                }

                if(isset($columnType) === false) 
                    die("The props '{$prop->getName()}' of Entity '{$entityName}' ah not @type.");
                
                $result = Entity::getDb()
                ->prepare(
                    replace(
                        "columnExist", 
                        [
                            "tableName" => $prefixedEntityName,
                            "columnName" => $columnName
                        ]
                    )
                ); 
                $result->execute();
                $result = $result->fetchAll(\PDO::FETCH_ASSOC);

                if(isset($result[0]) === false){
                    if($columnName === "id"){
                        $columnType = "SERIAL PRIMARY KEY";
                    }
                    $migrationSql .= replace(
                        "createColumn", 
                        [
                            "tableName" => $prefixedEntityName,
                            "columnName" => $columnName,
                            "columnType" => $columnType 
                                . ($isNotNullable === true? " NOT NULL" : "") 
                                . ($isUnique === true? " UNIQUE" : "")
                        ]
                    ) . "\n";
                }
                else {
                    if(strToLower($result[0]["data_type"]) !== strToLower($columnType)){
                        if($columnName === "id"){
                            $columnType = "SERIAL PRIMARY KEY";
                        }
                        $migrationSql .= replace(
                            "alterColumn", 
                            [
                                "tableName" => $prefixedEntityName,
                                "columnName" => $columnName,
                                "columnType" => $columnType
                            ]
                        ) . "\n";
                    }
                    if($result[0]["is_not_nullable"] !== $isNotNullable && $columnName !== "id"){
                        if($isNotNullable === true){
                            $migrationSql .= replace(
                                "alterColumnNotNull",
                                [
                                    "tableName" => $prefixedEntityName,
                                    "columnName" => $columnName
                                ]
                            ) . "\n";
                        }
                        else {
                            $migrationSql .= replace(
                                "alterColumnDropNotNull",
                                [
                                    "tableName" => $prefixedEntityName,
                                    "columnName" => $columnName
                                ]
                            ) . "\n";
                        }
                        
                    }
                    if($result[0]["is_unique"] !== $isUnique && $columnName !== "id"){
                        if($isUnique === true){
                            $migrationSql .= replace(
                                "alterColumnUnique", 
                                [
                                    "tableName" => $prefixedEntityName,
                                    "columnName" => $columnName
                                ]
                            ) . "\n";
                        }
                        else {
                            $migrationSql .= replace(
                                "alterColumnDropUnique", 
                                [
                                    "tableName" => $prefixedEntityName,
                                    "columnName" => $columnName
                                ]
                            ) . "\n";
                        }
                        
                    }
                }

                array_push($propsName, $columnName);
            }

            $result = Entity::getDb()
            ->prepare(
                replace(
                    "showColumns", 
                    [
                        "tableName" => $prefixedEntityName
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

$migrationFile = fopen(__DIR__ . "/../migration.sql", "w");
fwrite($migrationFile, $migrationSql);
fclose($migrationFile);