<?php

namespace Core;

use Exception;
use Core\Database;
use Core\ConfigFile;

#[\AllowDynamicProperties]
abstract class Entity implements \JsonSerializable
{

    static private \PDO $db;
    private array $props = [];
    private array $groups = [];

    public function __construct(array $array)
    {
        $this->props = $array;
    }

    public function __serialize()
    {
        return $this->toArray();
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    private function join(\ReflectionProperty $prop): bool
    {
        if ($prop->getType()->getName() === "array") {

            preg_match("/@many{(.*)}/", $prop->getDocComment(), $groups);

            $info = explode(",", $groups[1]);
            $targetEntity = $info[0] ?? null;
            $targetProp = $info[1] ?? null;

            if ($targetEntity === null || $targetProp === null) return false;

            $result = $targetEntity::findMany(["{$targetProp}_id" => $this->props["id"]]);

            $this->props[$prop->getName()] = $result;
            return true;
        } else {
            $propName = $prop->getName() . "_id";
            
            if (
                isset($this->props[$propName]) === false ||
                str_starts_with($prop->getType()->getName(), "Entity\\") === false
            ) return false;

            $propValue = $this->props[$propName];
            $entityName = explode("\\", $prop->getType()->getName());
            $entityName = "_" . array_pop($entityName);
            $targetEntity = $prop->getType()->getName();

            $result = $targetEntity::findFirst(["id" => $propValue]);
            
            if ($result === null) return false;

            $this->props[$prop->getName()] = $result;
            return true;
        }
    }

    public function toArray(): array
    {
        $array = [];

        $rp = new \ReflectionObject($this);
        foreach ($rp->getProperties(\ReflectionProperty::IS_PRIVATE) as $prop) {
            $propName = $prop->getName();
            $propType = $prop->getType()->getName();
            if (str_starts_with($propType, "Entity\\") === true || $propType === "array") {
                unset($this->props[$propName]);
                preg_match("/@groups{(.*)}/", $prop->getDocComment(), $groups);

                if (isset($groups[1]) === false) continue;

                $groups = explode(",", $groups[1]);
                foreach ($groups as $group) {
                    if (in_array($group, $this->groups) === true) {
                        $this->join($prop);
                        break;
                    }
                }
            }  

            if (array_key_exists($propName, $this->props) === false) continue;

            try {
                if (str_starts_with($prop->getType()->getName(), "Entity\\") === true) $array[$propName] = $this->props[$propName]->toArray();
                else $array[$propName] = $this->props[$propName];
            } catch (\Throwable $th) {
                continue;
            }
        }

        return $array;
    }

    public function groups(string ...$groups): void
    {
        $this->groups = $groups;
    }

    public function save(): void
    {
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        $props = [];

        $rp = new \ReflectionObject($this);
        foreach ($rp->getProperties(\ReflectionProperty::IS_PRIVATE) as $prop) {
            $propName = $prop->getName();
            if ($propName === "id" || $prop->getType()->getName() === "array") continue;
            else if (str_starts_with($prop->getType()->getName(), "Entity\\") === true){
                if(isset($this->props[$propName]) && gettype($this->props[$propName]) === "object"){
                    $props[$propName . "_id"] = $this->props[$propName]->get("id");
                    $this->props[$propName . "_id"] = $props[$propName . "_id"];
                }
                else if(isset($this->props[$propName . "_id"]) === true)$props[$propName . "_id"] = $this->props[$propName . "_id"];
                else $props[$propName . "_id"] = null;
            }
            else if (array_key_exists($propName, $this->props)) $props[$propName] = $this->props[$propName];
            else $this->props[$propName] = null;
        }

        if (array_key_exists("id", $this->props) === false) {
            $sqlRequest = QueryBuilder::createInsertRequest($currentEntityName, $props) . " RETURNING id";
            $result = self::$db->prepare($sqlRequest);
            $result->execute();
            $result = $result->fetchAll(\PDO::FETCH_ASSOC)[0];
            $this->props["id"] = $result["id"];
        } else {
            $sqlRequest = QueryBuilder::createUpdateRequest($currentEntityName, $props, ["id" => $this->props["id"]]);
            $result = self::$db->prepare($sqlRequest);
            $result->execute();
        }
    }

    public function delete()
    {
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        $sqlRequest = QueryBuilder::createDeleteRequest($currentEntityName, ["id" => $this->props["id"]]);
        $result = self::$db->prepare($sqlRequest);
        try {
            $result->execute();
        } catch (\Throwable $th) {
            return false;
        }

        $rp = new \ReflectionObject($this);
        foreach ($rp->getProperties(\ReflectionProperty::IS_PRIVATE) as $prop) {
            $propName = $prop->getName();
            $propType = $prop->getType()->getName();
            $propComment = $prop->getDocComment();
            if ($propType !== "array" && str_starts_with($propType, "Entity\\") === false) continue;
            else if (preg_match("/@cascade{}/", $propComment) === 0) continue;
            else if ($propType === "array") {
                foreach($this->get($propName) as $entity){
                    $entity->delete();
                }
            }
            else {
                $entity = $this->get($propName);
                if($entity === null) continue;
                $entity->delete();
            }
        }

        unset($this->props["id"]);
        return true;
    }

    protected function set(string $prop, mixed $value)
    {
        $prop = new \ReflectionProperty(static::class, $prop);

        if (str_starts_with($prop->getType()->getName(), "Entity\\") === true) {
            $this->props[$prop->getName() . "_id"] = $value->getId();
        } else $this->props[$prop->getName()] = $value;
    }

    protected function get(string $prop)
    {
        $prop = new \ReflectionProperty(static::class, $prop);

        if (str_starts_with($prop->getType()->getName(), "Entity\\") === true || $prop->getType()->getName() === "array") $this->join($prop);

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

        if (count($result) === 0) return null;

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

    static public function deleteMany(array $where = [])
    {
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

    static public function getDb()
    {
        return self::$db;
    }

    static public function dataBaseConnection()
    {
        $config = new ConfigFile();
        $database = new Database(
            $config->getEnv('DB_CONNECTION'),
            $config->getEnv('DB_HOST'),
            $config->getEnv('DB_PORT'),
            $config->getEnv('DB_DATABASE'),
            $config->getEnv('DB_USERNAME'),
            $config->getEnv('DB_PASSWORD')
        );
        self::$db = $database->connection();
    }
}

Entity::dataBaseConnection();
