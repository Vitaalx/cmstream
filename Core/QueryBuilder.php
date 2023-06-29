<?php
namespace Core;

class QueryBuilder {
    static private \PDO $db;
    
    static function createSelectRequest(string $from, array $select, array $where, array $options = []){
        $values = [];
        $select = self::arrayToArraySelect($select, $values);
        $where = self::arrayToArrayWhere($where, $values);
        $where = implode(" AND ", $where);
        $select = implode(", ", $select);
        $options = self::arrayToArrayOptions($options);
        $options = implode(" ", $options);
        
        $request = self::$db->prepare("SELECT " . $select . " FROM " . $from . ($where !== ""? " WHERE " : "") . $where . " ". $options);
        $request->execute($values);
        return $request;
    }

    static function createInsertRequest(string $to, array $tableValue, array $options = []){
        $keys = [];
        $values = [];
        $vs = [];

        foreach ($tableValue as $key => $value) {
            array_push($keys, $key);
            if(gettype($value) === "NULL"){
                $value = "NULL";
            }
            else if(gettype($value) === "boolean"){
                $value = ($value? " TRUE" : " FALSE");
            }
            array_push($vs, "?");
            array_push($values, $value);
        }

        $options = self::arrayToArrayOptions($options);
        $options = implode(" ", $options);

        $request = self::$db->prepare("INSERT INTO  " . $to . " ( " . implode(", ", $keys) . " ) VALUES ( " . implode(", ", $vs) . " ) " . $options);
        $request->execute($values);
        return $request;
    }

    static function createUpdateRequest(string $from, array $set, array $where, array $options = []){
        $values = [];
        $set = self::arrayToArraySet($set, $values);
        $where = self::arrayToArrayWhere($where, $values);
        $where = implode(" AND ", $where);
        $set = implode(", ", $set);
        $options = self::arrayToArrayOptions($options);
        $options = implode(" ", $options);

        $request = self::$db->prepare("UPDATE " . $from . " SET " . $set . ($where !== ""? " WHERE " : "") . $where . " " . $options);
        $request->execute($values);
        return $request;
        
    }

    static function createCountRequest(string $from, array $where, array $options = []){
        $values = [];
        $where = self::arrayToArrayWhere($where, $values);
        $where = implode(" AND ", $where);
        $options = self::arrayToArrayOptions($options);
        $options = implode(" ", $options);

        $request = self::$db->prepare("SELECT count(*) FROM " . $from . ($where !== ""? " WHERE " : "") . $where . " ". $options);
        $request->execute($values);
        return $request;
    }

    static function createDeleteRequest(string $from, array $where, array $options = []){
        $values = [];
        $where = self::arrayToArrayWhere($where, $values);
        $where = implode(" AND ", $where);
        $options = self::arrayToArrayOptions($options);
        $options = implode(" ", $options);

        $request = self::$db->prepare("DELETE FROM " . $from . ($where !== ""? " WHERE " : "") . $where . " " . $options);
        $request->execute($values);
        return $request;
    }

    static function arrayToArrayWhere(array $array, array &$values, string $operator = "="){
        $wheres = [];
        foreach($array as $key => $value){
            if($key === "\$OR"){
                $or = [];
                foreach($value as $v){
                    $orWhere = self::arrayToArrayWhere($v, $values);
                    $orWhere = "(" . implode(" AND ", $orWhere) . ")";
                    array_push($or, $orWhere);
                }
                array_push($wheres, "(" . implode(" OR ", $or) . ")");
            }
            else if($key === "\$BETWEEN"){
                array_push($values, $value[1], $value[2]);
                array_push($wheres, $value[0] . ($operator === "!=" ? " NOT" : "") . " BETWEEN ? AND ?");
            }
            else if($key === "\$NOT"){
                $not = self::arrayToArrayWhere($value, $values, "!=");
                $not = implode(" AND ", $not);
                array_push($wheres, $not);
            }
            else{
                $where = $key . " " . $operator . " ?";
                
                if(gettype($value) === "NULL"){
                    $where = $key . ($operator === "="? " IS NULL" : " IS NOT NULL");
                    array_push($wheres, $where);
                }
                else if(gettype($value) === "boolean"){
                    $where = $key . " " . $operator . ($value? " TRUE" : " FALSE");
                    array_push($wheres, $where);
                }
                else if(gettype($value) === "array"){
                    $where = "";
                    foreach ($value as $k => $v) {
                        if($k === "\$CTN"){
                            array_push($values, "%$v%");
                            array_push($wheres, $key . " LIKE ?");
                        }
                        else if($k === "\$SW"){
                            array_push($values, "$v%");
                            array_push($wheres, $key . " LIKE ?");
                        }
                        else if($k === "\$EW"){
                            array_push($values, "%$v");
                            $where .= $key . " LIKE ?";
                            array_push($wheres, $key . " LIKE ?");
                        }
                        else if($k === "\$GT"){
                            array_push($values, $v);
                            array_push($wheres, $key . " > ?");
                        }
                        else if($k === "\$GTE"){
                            array_push($values, $v);
                            array_push($wheres, $key . " >= ?");
                        }
                        else if($k === "\$LT"){
                            array_push($values, $v);
                            array_push($wheres, $key . " < ?");
                        }
                        else if($k === "\$LTE"){
                            array_push($values, $v);
                            array_push($wheres, $key . " <= ?");
                        }
                    }
                }
                else {
                    array_push($values, $value);
                    array_push($wheres, $where);
                }
            }
        }
    
        return $wheres;
    }

    static function arrayToArraySelect(array $array, array &$values){
        $selects = [];
        foreach($array as $key => $value){
            if($key === "\$CASE"){
                $case = [];
                foreach($value as $v){
                    if($v[0] === "when"){
                        $where = self::arrayToArrayWhere($v[1], $values);
                        array_push($case, "when " . implode(" AND ", $where) . " then " . $v[2]);

                    }
                    else if($v[0] === "else"){
                        array_push($case, $v[1]);
                    }
                    else if($v[0] === "end"){
                        array_push($case, "end as " . $v[1]);
                    }
                }
                array_push($selects, "CASE " . implode(" ", $case));
            }
            else {
                array_push($selects , $value);
            }
        }

        return $selects;
    }

    static function arrayToArrayOptions(array $array){
        $options = [];
        foreach($array as $key => $value){
            if($key === "ORDER_BY"){
                $orderOptions = [];

                foreach ($value as $v) {
                    $orderOptions[] = $v;
                }

                $options[] = "ORDER BY " . implode(", ", $orderOptions);
            }
            else if($key === "RETURNING") array_push($options, "RETURNING " . implode(", ", $value));
            else array_push($options, $key . " " . $value);
        }

        return $options;
    }

    static function arrayToArraySet(array $array, array &$values){
        $sets = [];
        foreach ($array as $key => $value) {
            if(gettype($value) === "NULL"){
                array_push($sets, $key . "= NULL");
            }
            else{
                array_push($values, $value);
                array_push($sets, $key . " = ?");
            }
            
        }
        return $sets;
    }

    static public function getDb(){
        return self::$db;
    }

    static public function dataBaseConnection(array $CONFIG): void
    {
        $pdo = new \PDO(
            $CONFIG["DB_TYPE"] .
            ":host=" . $CONFIG["DB_HOST"] .
            ";port=" . $CONFIG["DB_PORT"] .
            ";dbname=" . $CONFIG["DB_DATABASE"],
            $CONFIG["DB_USERNAME"],
            $CONFIG["DB_PASSWORD"]
        );
        self::$db = $pdo;
    }

}

if(isset(CONFIG["DB_HOST"]) === true)QueryBuilder::dataBaseConnection(CONFIG);
