<?php
namespace Core;

abstract class Entity{
    static private \PDO $db;

    public function __construct(){

    }

    static public function findOne($id): static
    {
        $currentEntity = explode("\\", static::class);
        $currentEntity = array_pop($currentEntity);

        $instance = new static();

        $result = self::$db->prepare("");
        $result = $result->execute();
        $result->fetchAll(\PDO::FETCH_ASSOC);

        $rp = new \ReflectionObject($instance);
        foreach($rp->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop){
            if(str_starts_with($prop->getType()->getName(), "Entity\\")){
                $propEntity = explode("\\", $prop->getType()->getName());
                $propEntity = array_pop($propEntity);

                $propIdName = $prop->getName() . "_id";
                $instance->$propIdName = $result->$propIdName;
                
            }
            else if($rp->getDocComment() !== false){
                preg_match("/@([a-z]*)\((.*)\)/", $rp->getDocComment(), $groups);

            }
            else {
                $propName = $prop->getName();
                $instance->$propName = $result->$propName;
            }
        }
        
        return $instance;
    }

    static public function getDb(){
        return self::$db;
    }

    static public function dataBaseConnection()
    {
        self::$db = new \PDO(
            "pgsql:host=database;port=5432;dbname=esgi",
            "esgi",
            "Test1234",
        );
    }
}

Entity::dataBaseConnection();