<?php
namespace Core;

// class QueryBuilder {
//     public string $entityName;
//     public string $where = "";
//     public string $select = "*";

//     public function __construct(string $entityName){
        
//     }

//     public function where(): QueryBuilderOperatorWhere
//     {
//         return new QueryBuilderOperatorWhere($this);
//     }

//     public function select(): QueryBuilderOperatorSelect
//     {
//         return new QueryBuilderOperatorSelect($this);
//     }

//     public function result(){
//         return "SELECT {$this->select} FROM {$this->entityName} WHERE {$this->where};";
//     }
// }

// class QueryBuilderOperatorWhere {
//     private QueryBuilder $queryBuilder;
//     public function __construct(QueryBuilder | QueryBuilderOperatorSelectCase $queryBuilder){
//         $this->queryBuilder = $queryBuilder;
//     }

//     public function equal(string $columnName, mixed $value): static
//     {
//         $this->queryBuilder->where .= "{$columnName} = {$value} ";
//         return $this;
//     }

//     public function moreThan(string $columnName, mixed $value): static
//     {
//         $this->queryBuilder->where .= "{$columnName} > {$value} ";
//         return $this;
//     }

//     public function lessThan(string $columnName, mixed $value): static
//     {
//         $this->queryBuilder->where .= "{$columnName} < {$value} ";
//         return $this;
//     }
    
//     public function and(): static
//     {
//         $this->queryBuilder->where .= "AND ";
//         return $this;
//     }

//     public function or(): static
//     {
//         $this->queryBuilder->where .= "OR ";
//         return $this;
//     }

//     public function between(string $columnName, mixed $first, mixed $second): static
//     {
//         $this->queryBuilder->where .= "{$columnName} BETWEEN {$first} AND {$second} ";
//         return $this;
//     }

//     public function then(string $action): static
//     {
//         $this->queryBuilder->where .= "THEN {$action} ";
//         return $this;
//     }
// }

// class QueryBuilderOperatorSelect {
//     private QueryBuilder $queryBuilder;
//     public function __construct(QueryBuilder $queryBuilder){
//         $this->queryBuilder = $queryBuilder;
//         $this->queryBuilder->select = "";
//     }

//     public function column(string $columnName, string $as = ""): static
//     {
//         $this->queryBuilder->select .= $columnName . ($as === ""? ", " : " as " . $as . ", ");
//         return $this;
//     }

//     public function case(QueryBuilderOperatorSelectCase $queryBuilderOperatorSelectCase): static
//     {
//         $this->queryBuilder->select .= $queryBuilderOperatorSelectCase->where . " ";
//         return $this;
//     }
// }

// class QueryBuilderOperatorSelectCase {
//     public string $where = "CASE ";
//     public function __construct(){
        
//     }

//     public function when(): QueryBuilderOperatorWhere
//     {
//         $this->where .= "WHEN ";
//         return new QueryBuilderOperatorWhere($this);
//     }

//     public function else(string $value)
//     {
//         $this->where .= "else {$value} ";
//     }

//     public function andAs(string $as): QueryBuilderOperatorSelectCase
//     {
//         $this->where .= "end as {$as}";
//         return $this;
//     }
// }

class QueryBuilder {
    static function createSelectRequest(string $from, array $select, array $where, array $join = []){
        $where = self::arrayToArrayWhere($where);
        $where = implode(" AND ", $where);
        $select = self::arrayToArraySelect($select);
        $select = implode(", ", $select);
        $join = self::arrayToArrayJoin($join, $from);
        $join = implode(" ", $join);
        return "SELECT " . $select . " FROM " . $from . " " . $join . " WHERE " . $where; 
    }

    static function createInsertRequest(string $to, array $tableValue){
        $keys = [];
        $values = [];

        foreach ($tableValue as $key => $value) {
            array_push($keys, $key);
            if(gettype($value) === "string"){
                $value = "'" . $value . "'";
            }
            array_push($values, $value);
        }
        return "INSERT INTO  " . $to . " ( " . implode(", ", $keys) . " ) VALUES ( " . implode(", ", $values) . " )"; 
    }

    static function arrayToArrayWhere(array $array){
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
            else{
                $where = $key . " = ";

                if(gettype($value) === "string"){
                    $where .= "'" . $value . "'";
                }
                else if(gettype($value) === "integer"){
                    $where .= $value;
                }
                else if(gettype($value) === "array"){

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

}
