<?php
namespace Core;

#[\AllowDynamicProperties]
abstract class Entity {
    static private \PDO $db;
    private int $id;
    private array $otherProps = [];

    public function __construct(array $array){
        foreach ($array as $key => $value) {
            if(property_exists($this, $key) === false){
                $this->otherProps[$key] = $value;
            }
            else {
                $setter = "set" . ucfirst($key);
                $this->$setter($value);
            }
        }
    }

    public function join(string $props): bool
    {
        $prop = new \ReflectionProperty(static::class, $props);
        $propName = $prop->getName() . "_id";
        if(isset($this->otherProps[$propName]) === false) return false;
        $propValue = $this->otherProps[$propName];
        $entity = explode("\\", $prop->getType()->getName());
        $entity = array_pop($entity);
        $class = $prop->getType()->getName();

        $sqlRequest = QueryBuilder::createSelectRequest("_" . $entity, ["*"], ["id" => $propValue]) . " LIMIT 1";
        $result = Entity::getDb()->prepare($sqlRequest);
        $result->execute();
        $result = $result->fetchAll(\PDO::FETCH_ASSOC)[0];

        $setter = "set" . ucfirst($prop->getName());
        $this->$setter(new $class($result));
        return true;
    }

    public function toArray(): array
    {
        $array = [];

        $rp = new \ReflectionObject($this);
        foreach($rp->getProperties(\ReflectionProperty::IS_PRIVATE) as $prop){
            $propName = $prop->getName();
            $getter = "get" . ucfirst($propName);
            try{
                if(str_starts_with($prop->getType()->getName(), "Entity\\") === true)$array[$propName] = $this->$getter()->toArray();
                else $array[$propName] = $this->$getter();
            }
            catch(\Throwable $th){
                continue;
            }
        }

        return $array;
    }

    public function save(): void
    {
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        $props = [];

        $rp = new \ReflectionObject($this);
        foreach($rp->getProperties(\ReflectionProperty::IS_PRIVATE) as $prop){
            $propName = $prop->getName();
            $getter = "get" . ucfirst($propName);
            try{
                if(str_starts_with($prop->getType()->getName(), "Entity\\") === true) $props[$propName . "_id"] = $this->otherProps[$propName . "_id"];
                else $props[$propName] = $this->$getter();
            }
            catch(\Throwable $th){
                continue;
            }
        }

        if(isset($this->id) === false){
            $sqlRequest = QueryBuilder::createInsertRequest($currentEntityName, $props);
            $result = self::$db->prepare($sqlRequest);
            $result->execute();
        }
    }

    static public function findOne(array $where): ?static
    {
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        $sqlRequest = QueryBuilder::createSelectRequest($currentEntityName, ["*"], $where) . " LIMIT 1";
        $result = self::$db->prepare($sqlRequest);
        $result->execute();
        $result = $result->fetchAll(\PDO::FETCH_ASSOC);

        if(count($result) === 0) return null;

        $instance = new static($result[0]);
        
        return $instance;
    }

    static public function insert(array $default): ?static
    {
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        $instance = new static($default);
        $instance->save();

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