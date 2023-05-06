<?php
namespace Core;

class QueryBuilder {
    public string $entityName;
    public string $where = "";
    public string $select = "*";

    public function __construct(string $entityName){
        
    }

    public function where(): QueryBuilderOperatorWhere
    {
        return new QueryBuilderOperatorWhere($this);
    }

    public function select(): QueryBuilderOperatorSelect
    {
        return new QueryBuilderOperatorSelect($this);
    }

    public function result(){
        return "SELECT {$this->select} FROM {$this->entityName} WHERE {$this->where};";
    }
}

class QueryBuilderOperatorWhere {
    private QueryBuilder $queryBuilder;
    public function __construct(QueryBuilder | QueryBuilderOperatorSelectCase $queryBuilder){
        $this->queryBuilder = $queryBuilder;
    }

    public function equal(string $columnName, mixed $value): static
    {
        $this->queryBuilder->where .= "{$columnName} = {$value} ";
        return $this;
    }

    public function moreThan(string $columnName, mixed $value): static
    {
        $this->queryBuilder->where .= "{$columnName} > {$value} ";
        return $this;
    }

    public function lessThan(string $columnName, mixed $value): static
    {
        $this->queryBuilder->where .= "{$columnName} < {$value} ";
        return $this;
    }
    
    public function and(): static
    {
        $this->queryBuilder->where .= "AND ";
        return $this;
    }

    public function or(): static
    {
        $this->queryBuilder->where .= "OR ";
        return $this;
    }

    public function between(string $columnName, mixed $first, mixed $second): static
    {
        $this->queryBuilder->where .= "{$columnName} BETWEEN {$first} AND {$second} ";
        return $this;
    }

    public function then(string $action): static
    {
        $this->queryBuilder->where .= "THEN {$action} ";
        return $this;
    }
}

class QueryBuilderOperatorSelect {
    private QueryBuilder $queryBuilder;
    public function __construct(QueryBuilder $queryBuilder){
        $this->queryBuilder = $queryBuilder;
        $this->queryBuilder->select = "";
    }

    public function column(string $columnName, string $as = ""): static
    {
        $this->queryBuilder->select .= $columnName . ($as === ""? ", " : " as " . $as . ", ");
        return $this;
    }

    public function case(QueryBuilderOperatorSelectCase $queryBuilderOperatorSelectCase): static
    {
        $this->queryBuilder->select .= $queryBuilderOperatorSelectCase->where . " ";
        return $this;
    }
}

class QueryBuilderOperatorSelectCase {
    public string $where = "CASE ";
    public function __construct(){
        
    }

    public function when(): QueryBuilderOperatorWhere
    {
        $this->where .= "WHEN ";
        return new QueryBuilderOperatorWhere($this);
    }

    public function else(string $value)
    {
        $this->where .= "else {$value} ";
    }

    public function andAs(string $as): QueryBuilderOperatorSelectCase
    {
        $this->where .= "end as {$as}";
        return $this;
    }
}