<?php

namespace Core;

#[\AllowDynamicProperties]
abstract class Entity implements \JsonSerializable
{
    static private \PDO $db;
    static private array $groups = [];
    private static array $reflections = [];
    private array $props = [];
    private string $entityName;

    public function __construct(array $array)
    {
        if (isset(self::$reflections[static::class]) === false) {
            self::$reflections[static::class] = [];
            $rp = new \ReflectionObject($this);
            foreach ($rp->getProperties(\ReflectionProperty::IS_PRIVATE) as $prop) {
                $type = $prop->getType()->getName();
                $name = $prop->getName();
                $comment = $prop->getDocComment();

                self::$reflections[static::class][$name] = [
                    "type" => $type,
                    "name" => $name,
                    "entityProp" => null,
                    "groups" => null,
                    "many" => null,
                    "cascade" => null,
                    "default" => null,
                ];

                if (str_starts_with($type, "Entity\\")) {
                    $entityProp = explode("\\", $type);
                    self::$reflections[static::class][$name]["entityProp"] = array_pop($entityProp);
                }

                preg_match_all("/@(.*){(.*)}/", $comment, $groups);

                foreach ($groups[1] as $key => $value) {
                    if ($value === "many") self::$reflections[static::class][$name][$value] = explode(",", $groups[2][$key]);
                    else if ($value === "groups") self::$reflections[static::class][$name][$value] = explode(",", $groups[2][$key]);
                    else if ($value === "cascade") self::$reflections[static::class][$name][$value] = true;
                    else if ($value === "default") self::$reflections[static::class][$name][$value] = true;
                }
            }
        }

        $split = explode("\\", static::class);
        $this->entityName = array_pop($split);

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

    private function join(array $prop): bool
    {
        if ($prop["type"] === "array") {
            $targetEntity = $prop["many"][0] ?? null;
            $targetProp = $prop["many"][1] ?? null;

            if ($targetEntity === null || $targetProp === null) return false;

            $result = $targetEntity::findMany(["{$targetProp}_id" => $this->props["id"]]);

            $this->props[$prop["name"]] = $result;
            return true;
        } else {
            $propName = $prop["name"] . "_id";

            if (
                isset($this->props[$propName]) === false ||
                $prop["entityProp"] === null
            ) return false;

            $propValue = $this->props[$propName];
            $targetEntity = $prop["type"];

            $result = $targetEntity::findFirst(["id" => $propValue]);

            if ($result === null) return false;

            $this->props[$prop["name"]] = $result;
            return true;
        }
    }

    public function toArray(): array
    {
        $array = [];

        foreach (self::$reflections[static::class] as $propName => $prop) {
            if ($prop["groups"] !== null) {
                $hasGroupe = false;
                foreach ($prop["groups"] as $group) {
                    if (in_array($group, self::$groups) === true) {
                        $hasGroupe = true;
                        break;
                    }
                }
                if ($hasGroupe === true && ($prop["entityProp"] !== null || $prop["type"] === "array")) $this->join($prop);
                else if ($hasGroupe === false) continue;
            } else if ($prop["entityProp"] !== null || $prop["type"] === "array") continue;

            try {
                if (isset($this->props[$propName]) === false) continue;
                else if ($prop["entityProp"] !== null) $array[$propName] = $this->props[$propName]->toArray();
                else $array[$propName] = $this->props[$propName];
            } catch (\Throwable $th) {
                continue;
            }
        }

        return $array;
    }

    public function save(): void
    {
        $currentEntityName = "_" . $this->entityName;

        $props = [];
        $returning = ["id"];

        foreach (self::$reflections[static::class] as $propName => $prop) {
            if ($propName === "id" || $prop["type"] === "array") continue;
            else if ($prop["entityProp"] !== null) {
                if (isset($this->props[$propName]) && gettype($this->props[$propName]) === "object") {
                    $props[$propName . "_id"] = $this->props[$propName]->get("id");
                    $this->props[$propName . "_id"] = $props[$propName . "_id"];
                } else if (isset($this->props[$propName . "_id"]) === true) $props[$propName . "_id"] = $this->props[$propName . "_id"];
                else $props[$propName . "_id"] = null;
            } else if (array_key_exists($propName, $this->props)) $props[$propName] = $this->props[$propName];
            else $this->props[$propName] = null;

            if ($prop["default"] === true) array_push($returning, $propName);
        }

        if (array_key_exists("id", $this->props) === false) {
            $sqlRequest = QueryBuilder::createInsertRequest($currentEntityName, $props, ["RETURNING" => $returning]);
            $result = self::$db->prepare($sqlRequest);
            $result->execute();
            $result = $result->fetchAll(\PDO::FETCH_ASSOC)[0];
            foreach ($result as $key => $value) {
                $this->props[$key] = $value;
            }
        } else {
            $sqlRequest = QueryBuilder::createUpdateRequest($currentEntityName, $props, ["id" => $this->props["id"]]);
            $result = self::$db->prepare($sqlRequest);
            $result->execute();
        }
    }

    public function delete(): bool
    {
        $currentEntityName = "_" . $this->entityName;

        $sqlRequest = QueryBuilder::createDeleteRequest($currentEntityName, ["id" => $this->props["id"]]);
        $result = self::$db->prepare($sqlRequest);
        try {
            $result->execute();
        } catch (\Throwable $th) {
            return false;
        }

        foreach (self::$reflections[static::class] as $propName => $prop) {
            if ($prop["type"] !== "array" && $prop["entityProp"] === null) continue;
            else if ($prop["cascade"] === null) continue;
            else if ($prop["type"] === "array") {
                foreach ($this->get($propName) as $entity) {
                    $entity->delete();
                }
            } else {
                $entity = $this->get($propName);
                if ($entity === null) continue;
                $entity->delete();
            }
        }

        unset($this->props["id"]);
        return true;
    }

    protected function set(string $prop, mixed $value): void
    {
        $prop = self::$reflections[static::class][$prop];

        if ($prop["entityProp"] !== null) {
            $this->props[$prop["name"] . "_id"] = $value->getId();
        } else $this->props[$prop["name"]] = $value;
    }

    protected function get(string $prop)
    {
        $prop = self::$reflections[static::class][$prop];

        if ($prop["entityProp"] !== null || $prop["type"] === "array") $this->join($prop);

        return $this->props[$prop["name"]];
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

    static public function deleteMany(array $where = []): bool
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

    static public function groups(string ...$groups): void
    {
        self::$groups = $groups;
    }

    static public function getDb() : \PDO
    {
        return self::$db;
    }

    static public function dataBaseConnection(array $CONFIG): void
    {
        $pdo = new \PDO(
            $CONFIG["DB_CONNECTION"] .
            ":host=" . $CONFIG["DB_HOST"] .
            ";port=" . $CONFIG["DB_PORT"] .
            ";dbname=" . $CONFIG["DB_DATABASE"],
            $CONFIG["DB_USERNAME"],
            $CONFIG["DB_PASSWORD"]
        );
        self::$db = $pdo;
    }
}

if(isset(CONFIG["DB_CONNECTION"]) === true)Entity::dataBaseConnection(CONFIG);

