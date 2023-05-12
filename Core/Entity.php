<?php
namespace Core;

use Exception;

#[\AllowDynamicProperties]
abstract class Entity implements \JsonSerializable{
    static private \PDO $db;
    private ?int $id = null;
    private array $props = [];

    public function __construct(array $array){
        $this->props = $array;
    }

    public function __serialize(){
        return $this->toArray();
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(){
        return $this->toArray();
    }

    public function join(string $prop): bool
    {
        $prop = new \ReflectionProperty(static::class, $prop);

        if($prop->getType()->getName() === "array"){

            preg_match_all("/@([a-z]*){(.*)}/", $prop->getDocComment(), $groups);
            $targetEntity = null;
            $targetProp = null;

            foreach ($groups[1] ?? [] as $key => $value) {
                if($value === "many"){
                    $info = explode(",", $groups[2][$key]);
                    $targetEntity = $info[0] ?? null;
                    $targetProp = $info[1] ?? null;
                }
            }

            if($targetEntity === null || $targetProp === null) return false;

            $result = $targetEntity::findMany(["{$targetProp}_id" => $this->props["id"]]);
            
            $this->props[$prop->getName()] = $result;
            return true;
        }
        else {
            $propName = $prop->getName() . "_id";

            if(
                isset($this->props[$propName]) === false ||
                str_starts_with($prop->getType()->getName(), "Entity\\") === false
            ) return false;
            
            $propValue = $this->props[$propName];
            $entityName = explode("\\", $prop->getType()->getName());
            $entityName = "_" . array_pop($entityName);
            $targetEntity = $prop->getType()->getName();

            $result = $targetEntity::findFirst(["id" => $propValue]);

            if($result === null) return false;

            $this->props[$prop->getName()] = $result;
            return true;
        }
    }

    public function toArray(): array
    {
        $array = [];

        $rp = new \ReflectionObject($this);
        foreach($rp->getProperties(\ReflectionProperty::IS_PRIVATE) as $prop){
            $propName = $prop->getName();
            if(array_key_exists($propName, $this->props) === false) continue;

            try{
                if(str_starts_with($prop->getType()->getName(), "Entity\\") === true)$array[$propName] = $this->props[$propName]->toArray();
                else $array[$propName] = $this->props[$propName];
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
            if($propName === "id") continue;
            else if(str_starts_with($prop->getType()->getName(), "Entity\\") === true) $props[$propName . "_id"] = $this->props[$propName . "_id"];
            else if(array_key_exists($propName, $this->props))$props[$propName] = $this->props[$propName];
            else $this->props[$propName] = null;

        }
        
        if(array_key_exists("id", $this->props) === false){
            $sqlRequest = QueryBuilder::createInsertRequest($currentEntityName, $props) . " RETURNING id";
            $result = self::$db->prepare($sqlRequest);
            $result->execute();
            $result = $result->fetchAll(\PDO::FETCH_ASSOC)[0];
            $this->props["id"] = $result["id"];
        }
        else {
            $sqlRequest = QueryBuilder::createUpdateRequest($currentEntityName, $props, ["id" => $this->props["id"]]);
            $result = self::$db->prepare($sqlRequest);
            $result->execute();
        }
    }

    public function delete(){
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        $sqlRequest = QueryBuilder::createDeleteRequest($currentEntityName, ["id" => $this->props["id"]]);
        $result = self::$db->prepare($sqlRequest);
        try {
            $result->execute();
        } catch (\Throwable $th) {
            return false;
        }

        unset($this->props["id"]);
        return true;
    }

    protected function set(string $prop, mixed $value){
        $prop = new \ReflectionProperty(static::class, $prop);

        $this->props[$prop->getName()] = $value;
    }

    protected function get(string $prop){
        $prop = new \ReflectionProperty(static::class, $prop);

        return $this->props[$prop->getName()];
    }

    static public function findFirst(array $where = []): ?static
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

    static public function findMany(array $where = []): array
    {
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        $sqlRequest = QueryBuilder::createSelectRequest($currentEntityName, ["*"], $where);
        $result = self::$db->prepare($sqlRequest);
        $result->execute();
        $result = $result->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key => $value) {
            $result[$key] = new static($value);
        }
        
        return $result;
    }

    static public function insertOne(array $default): ?static
    {
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        $instance = new static($default);
        $instance->save();

        return $instance;
    }

    static public function deleteMany(array $where = []){
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        $sqlRequest = QueryBuilder::createDeleteRequest($currentEntityName, $where);
        $result = self::$db->prepare($sqlRequest);
        try {
            $result->execute();
        } catch (\Throwable $th) {
            return false;
        }

        return true;
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