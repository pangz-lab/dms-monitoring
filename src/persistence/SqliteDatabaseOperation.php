<?php

namespace PangzLab\DMSMonitoring\Persistence;

class SqliteDatabaseOperation
{
    private $db;
    private $lastError;

    public function __construct(SqliteDatabase $db)
    {
        $db->connect();
        $this->db = $db->instance();
    }

    public function insert(DatabaseParameter $param, $withColumns = false, $ignoreOnFailure = false): bool
    {
        try {
            $columns = $withColumns? "({$param->columns()})" : "";
            $ignore  = $ignoreOnFailure? "OR IGNORE" : "";
            $sql     = "INSERT $ignore INTO {$param->table()} $columns VALUES ({$param->valueKeyBinding()})";
            
            // print "******* ".$sql." *******";
            $stmt = $this->db->prepare($sql);
            $this->iterateBinding($stmt, $param->columnValues());
            
            $stmt->execute();
            print "inserted ID  >> ".$this->lastInsertId();
            return ($this->db->lastErrorCode() > 0)? false : true;

        } catch (\Exception $e) {
            throw new \Exception("[Insertion Error] Failed to insert :".$e->getMessage());
        }
    }

    public function select(DatabaseParameter $param, int $resultType = SQLITE3_ASSOC): array
    {
        try {
            $data      = [];
            $columns   = (empty($param->columns()))? "*" : $param->columns();
            $orderBy   = (empty($param->orderByColumns()))? "" : "ORDER BY ".$param->orderByColumns()." ".$param->orderBy();
            $condition = (!empty($param->condition()))? "WHERE {$param->condition()}" : "";
            $limit     = (empty($param->rowLimit()))? "" : "LIMIT ".$param->rowLimit();
            $sql       = "SELECT $columns FROM {$param->table()} {$condition} {$orderBy} {$limit}";
            
            // print "******* ".$sql." *******";
            $stmt = $this->db->prepare($sql);
            $this->iterateBinding($stmt, $param->columnValues());
            $result = $stmt->execute();
            while($row = $result->fetchArray($resultType)) {
                $data[] = $row;
            }
            
            return $data;
            
        } catch (\Exception $e) {
            throw new \Exception("[Select Error] Failed to select :".$e->getMessage());
        }
    }

    public function selectCount(DatabaseParameter $param): int
    {
        try {
            $condition = (!empty($param->condition()))? "WHERE {$param->condition()}" : "";
            $sql       = "SELECT COUNT(*) as COUNT FROM {$param->table()} $condition";
            
            $stmt = $this->db->prepare($sql);
            $this->iterateBinding($stmt, $param->columnValues());
            $result = $stmt->execute();
            $row    = $result->fetchArray(SQLITE3_ASSOC);
            return $row["COUNT"] ?? 0;
            
        } catch (\Exception $e) {
            throw new \Exception("[Select Error] Failed to select :".$e->getMessage());
        }
    }

    public function update(DatabaseParameter $param): bool
    {
        try {
            $condition = (!empty($param->condition())) ? "WHERE {$param->condition()}" : "";
            $sql       = "UPDATE {$param->table()} SET {$param->columns()} $condition";
            
            // print "******* ".$sql." *******";
            $stmt = $this->db->prepare($sql);
            $this->iterateBinding($stmt, $param->columnValues());
            $result = $stmt->execute();
            return ($this->db->lastErrorCode() > 0)? false : true;
            
        } catch (\Exception $e) {
            throw new \Exception("[Update Error] Failed to update :".$e->getMessage());
        }
    }

    public function lastInsertId(): int
    {
        return $this->db->lastInsertRowID();
    }

    private function iterateBinding($stmt, array $columnValue): void
    {
        foreach($columnValue as $key => $value) {
            print (" $key => $value \n");
            $stmt->bindValue(':'.$key, $value);
        }
    }
}