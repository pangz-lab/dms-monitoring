<?php

namespace PangzLab\DMSMonitoring\Persistence;

class DatabaseParameter
{
    private $table;
    private $columnValue;
    private $columns;
    private $values;
    private $condition;
    private $rowLimit;
    private $orderBy;
    private $orderByColumns;

    function __construct(
        $params = array(
        "table" => "",
        "columns" => array(),
        "condition" => "",
        "columnValue" => array(),
        "rowLimit" => array(),
        "orderBy" => array()
    )) {
        $this->table       = $params["table"] ?? "";
        $this->columnValue = $params["columnValue"] ?? [];
        $this->condition   = $params["condition"] ?? "";
        $this->rowLimit    = $params["rowLimit"] ?? [];
        $this->orderByColumns = $params["orderByColumns"] ?? [];
        $this->orderBy        = $params["orderBy"] ?? "desc";
        $this->columns        = $params["columns"] ?? \array_keys($this->columnValue) ?? [];
        $this->values         = \array_values($this->columnValue) ?? [];
    }

    public function setColumns(array $cols): DatabaseParameter
    {
        $this->columns = $cols;
        return $this;
    }

    public function setCondition(string $condition): DatabaseParameter
    {
        $this->condition = $condition;
        return $this;
    }

    public function table(): string
    {
        return $this->table;
    }

    public function columnValues(): array
    {
        return $this->columnValue;
    }

    public function columns(): string
    {
        return implode(",", $this->columns);
    }

    public function rowLimit(): string
    {
        return implode(",", $this->rowLimit);
    }
    
    public function orderByColumns(): string
    {
        return implode(",", $this->orderByColumns);
    }

    public function orderBy(): string
    {
        return $this->orderBy;
    }
    
    public function values(): string
    {
        return implode(",", $this->values);
    }

    public function valueKeyBinding(): string
    {
        return implode(",",
            array_map(function($i) {
                return ":".$i;
                }, $this->columns
            )
        );
    }

    public function condition(): string
    {
        return $this->condition;
    }
}