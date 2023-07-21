<?php

namespace Core;

use Iterator;

#[\AllowDynamicProperties]
abstract class Entity implements \JsonSerializable
{
    static protected array $groups = [];
    private static array $reflections = [];
    private array $props = [];
    private array $propsChange = [];
    private string $entityName;

    public function __construct(array $array)
    {
        // singleton pattern to avoid multiple reflection
        if (array_key_exists(static::class, self::$reflections) === false) {
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

    abstract public function getId(): int;

    public function __serialize()
    {
        return $this->onSerialize($this->toArray());
    }

    /**
     * @return array<string, mixed>
     * transform object to array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->onSerialize($this->toArray());
    }

    protected function onSerialize(array $array): array
    {
        return $array;
    }

    /**
     * @param array $prop
     * @return boolean
     * Join the property if it is an entity or an array
     */
    private function join(array $prop): bool
    {
        if ($prop["type"] === "array") {
            $targetEntity = $prop["many"][0] ?? null;
            $targetProp = $prop["many"][1] ?? null;

            if ($targetEntity === null || $targetProp === null) return false;

            $result = $targetEntity::findMany(["{$targetProp}_id" => $this->props["id"]]);

            $this->props[$prop["name"]] = $result;
            return true;
        } 
        else {
            $propName = $prop["name"] . "_id";

            if (
                array_key_exists($propName, $this->props) === false ||
                $prop["entityProp"] === null
            ) return false;

            if($this->props[$propName] === null){
                $this->props[$prop["name"]] = null;
                return true;
            }

            $propValue = $this->props[$propName];
            $targetEntity = $prop["type"];

            $result = $targetEntity::findFirst(["id" => $propValue]);

            $this->props[$prop["name"]] = $result;
            if ($result === null) return false;

            return true;
        }
    }

    /**
     * @return array
     * 
     * If the property is array, the array is join
     * 
     * @throws \Throwable
     */
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
                if (array_key_exists($propName, $this->props) === false) continue;
                else $array[$propName] = $this->props[$propName];
            } catch (\Throwable $th) {
                continue;
            }
        }

        return $array;
    }
    
    /**
     * @return void
     * 
     * Save the current entity.
     */
    public function save(): void
    {
        $isInsert = !array_key_exists("id", $this->props);
        $this->onSave($isInsert);

        $currentEntityName = "_" . $this->entityName;

        $props = [];
        $returning = ["id"];

        foreach (self::$reflections[static::class] as $propName => $prop) {
            if (
                $propName === "id" || 
                $prop["type"] === "array" || 
                (
                    array_key_exists($propName, $this->propsChange) === false &&
                    array_key_exists("id", $this->props) === true
                )
            ) continue;
            else if ($prop["entityProp"] !== null) {
                if (array_key_exists($propName, $this->props) && gettype($this->props[$propName]) === "object") {
                    $props[$propName . "_id"] = $this->props[$propName]->get("id");
                    $this->props[$propName . "_id"] = $props[$propName . "_id"];
                } else if (array_key_exists($propName . "_id", $this->props) === true) $props[$propName . "_id"] = $this->props[$propName . "_id"];
                else $props[$propName . "_id"] = null;
            } else if (array_key_exists($propName, $this->props)) $props[$propName] = $this->props[$propName];
            else $this->props[$propName] = null;

            if ($prop["default"] === true) array_push($returning, $propName);
        }

        if ($isInsert === true) {
            $request = QueryBuilder::createInsertRequest($currentEntityName, $props, ["RETURNING" => $returning]);
            $result = $request->fetchAll(\PDO::FETCH_ASSOC)[0];
            foreach ($result as $key => $value) {
                $this->props[$key] = $value;
            }
        }
        else if(count($props) === 0) return; 
        else {
            QueryBuilder::createUpdateRequest($currentEntityName, $props, ["id" => $this->props["id"]]);
        }

        $this->propsChange = [];
    }

    protected function onSave(bool $isInsert): void
    {
        
    }

    /**
     * @return boolean
     * 
     * Delete the current entity.
     * If the entity is join to another entity, the other entity is also deleted.
     */
    public function delete(): bool
    {
        $this->onDelete();
        $currentEntityName = "_" . $this->entityName;
        try {
            QueryBuilder::createDeleteRequest($currentEntityName, ["id" => $this->props["id"]]);
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

    protected function onDelete()
    {
        
    }

    /**
     * @param string $prop
     * @param mixed $value
     * @return void
     * 
     * Get Property of Object.
     * Set value of property.
     * If the property is an entity or an array, the id of the entity is set.
     * Otherwise the value is set.
     * The property is marked as changed.
     */
    protected function set(string $prop, mixed $value): void
    {
        $prop = self::$reflections[static::class][$prop];
        if ($prop["entityProp"] !== null){
            $this->props[$prop["name"] . "_id"] = $value !== null ? $value->getId() : null;
        } 
        else $this->props[$prop["name"]] = $value;

        $this->propsChange[$prop["name"]] = true;
    }

    /**
     * @param string $propName
     * @return mixed
     * 
     * Get Property of Object
     * Join if the property is an entity or an array then return the property
     * Otherwise return the property
     */
    protected function get(string $propName)
    {
        $prop = self::$reflections[static::class][$propName] ?? null;

        if($prop === null) return $this->props[$propName];
        else if ($prop["entityProp"] !== null || $prop["type"] === "array") $this->join($prop);

        return $this->props[$prop["name"]];
    }

    /**
     * @param array $where
     * @param array $options
     * @return static|null
     * 
     * If no options are specified, the LIMIT option is set to 1
     * If no result is found, null is returned
     * Otherwise the first result formated as an instance of the current class is returned
     */
    static public function findFirst(array $where = [], array $options = []): ?static
    {
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        if(isset($options["LIMIT"]) === false) $options["LIMIT"] = 1;

        $request = QueryBuilder::createSelectRequest($currentEntityName, ["*"], $where, $options);
        $result = $request->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) === 0) return null;

        $instance = new static($result[0]);

        return $instance;
    }

    /**
     * @param array $where
     * @param array $options
     *  @return static[]
     * 
     * return an array of instances of the current class
     */
    static public function findMany(array $where = [], array $options = []): array
    {
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        $request = QueryBuilder::createSelectRequest($currentEntityName, ["*"], $where, $options);
        $result = $request->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key => $value) {
            $result[$key] = new static($value);
        }

        return $result;
    }

    /**
     * Undocumented function
     *
     * @param array $where
     * @param array $options
     * @return EntityIterator
     * 
     * return an iterator of instances of the current class
     */
    static public function findIterator(array $where = [], array $options = []): EntityIterator
    {
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        $request = QueryBuilder::createSelectRequest($currentEntityName, ["*"], $where, $options);
        return new EntityIterator(static::class, $request->getIterator());
    }

    static public function insertOne(callable | array $default): ?static
    {
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        if(is_callable($default) === true){
            $instance = new static([]);
            $default($instance);
        }
        else $instance = new static($default);

        $instance->save();

        return $instance;
    }

    /**
     * @param array $where
     * @return boolean
     * 
     * Delete all rows matching the where clause.
     * If no where clause is specified, all rows are deleted.
     * Return true if the request is successful, false otherwise.
     */
    static public function deleteMany(array $where = []): bool
    {
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        try {
            QueryBuilder::createDeleteRequest($currentEntityName, $where);
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }

    /**
     * @param array $where
     * @return integer
     * 
     * return the number of rows found
     */
    static public function count(array $where = []): int
    {
        $currentEntityName = explode("\\", static::class);
        $currentEntityName = "_" . array_pop($currentEntityName);

        $request = QueryBuilder::createCountRequest($currentEntityName, $where);
        $result = $request->fetchColumn();

        return $result;
    }

    static public function groups(string ...$groups): void
    {
        self::$groups = $groups;
    }
}

// Interface for external iterators or objects that can be iterated themselves internally.
// Allows iteration over entities
class EntityIterator implements Iterator
{
    public Iterator $iterator;
    public string $entity;

    public function __construct(string $entity, Iterator $iterator){
        $this->iterator = $iterator;
        $this->entity = $entity;
    }

    public function current(): mixed
    {
        return new $this->entity($this->iterator->current());
    }

    public function key(): mixed
    {
        return $this->iterator->key();
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function rewind(): void
    {
        $this->iterator->rewind();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }
}