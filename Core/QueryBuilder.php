<?php
namespace Core;

class QueryBuilder {
    static function createSelectRequest(string $from, array $select, array $where, array $join = []){
        $where = self::arrayToArrayWhere($where);
        $where = implode(" AND ", $where);
        $select = self::arrayToArraySelect($select);
        $select = implode(", ", $select);
        $join = self::arrayToArrayJoin($join, $from);
        $join = implode(" ", $join);
        return "SELECT " . $select . " FROM " . $from . " " . $join . ($where !== ""? " WHERE " : "") . $where; 
    }

    static function createInsertRequest(string $to, array $tableValue){
        $keys = [];
        $values = [];

        foreach ($tableValue as $key => $value) {
            array_push($keys, $key);
            if(gettype($value) === "string"){
                $value = "'" . $value . "'";
            }
            else if(gettype($value) === "NULL"){
                $value = "NULL";
            }
            array_push($values, $value);
        }
        return "INSERT INTO  " . $to . " ( " . implode(", ", $keys) . " ) VALUES ( " . implode(", ", $values) . " )"; 
    }

    static function createUpdateRequest(string $from, array $set, array $where){
        $where = self::arrayToArrayWhere($where);
        $where = implode(" AND ", $where);
        $set = self::arrayToArraySet($set);
        $set = implode(", ", $set);
        return "UPDATE " . $from . " SET " . $set . ($where !== ""? " WHERE " : "") . $where;
    }

    static function createDeleteRequest(string $from, array $where){
        $where = self::arrayToArrayWhere($where);
        $where = implode(" AND ", $where);
        return "DELETE FROM " . $from . ($where !== ""? " WHERE " : "") . $where;
    }

    static function arrayToArrayWhere(array $array, string $operator = "="){
        $wheres = [];
        foreach($array as $key => $value){
            if($key === "\$OR"){
                $or = [];
                foreach($value as $v){
                    $orWhere = self::arrayToArrayWhere($v);
                    $orWhere = "(" . implode(" AND ", $orWhere) . ")";
                    array_push($or, $orWhere);
                }
                array_push($wheres, "(" . implode(" OR ", $or) . ")");
            }
            else if($key === "\$BETWEEN"){
                array_push($wheres, $value[0] . " BETWEEN " . $value[1] . " AND " . $value[2]);
            }
            else if($key === "\$NOT"){
                $not = self::arrayToArrayWhere($value, "!=");
                $not = implode(" AND ", $not);
                array_push($wheres, $not);
            }
            else{
                $where = $key . " " . $operator . " ";

                if(gettype($value) === "string"){
                    $where .= "'" . $value . "'";
                }
                else if(gettype($value) === "integer"){
                    $where .= $value;
                }
                else if(gettype($value) === "NULL"){
                    $where = $key . ($operator === "="? " IS NULL" : " IS NOT NULL");
                }
                else if(gettype($value) === "array"){
                    $where = [];
                    foreach ($value as $value) {
                        $w = self::arrayToArrayWhere(["$key" => $value], $operator);
                        array_push($where, $w[0]);
                    }
                    $where = implode(" AND ", $where);
                }

                array_push($wheres, $where);
            }
        }
    
        return $wheres;
    }

    static function arrayToArraySelect(array $array){
        $selects = [];
        foreach($array as $key => $value){
            if($key === "\$CASE"){
                $case = [];
                foreach($value as $v){
                    if($v[0] === "when"){
                        $where = self::arrayToArrayWhere($v[1]);
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

    static function arrayToArrayJoin(array $array, string $from){
        $joins = [];
        foreach ($array as $key => $value) {
            array_push($joins, "JOIN " . $key . " on " . $from . "." . $value[0] . " = " . $key . "." . $value[1]);
        }
        return $joins;
    }

    static function arrayToArraySet(array $array){
        $sets = [];
        foreach ($array as $key => $value) {
            if(gettype($value) === "string"){
                array_push($sets, $key . " = '" . $value . "'");
            }
            else if(gettype($value) === "integer"){
                array_push($sets, $key . " = " . $value);
            }
            else if(gettype($value) === "NULL"){
                array_push($sets, $key . "= NULL");
            }
        }
        return $sets;
    }

}
