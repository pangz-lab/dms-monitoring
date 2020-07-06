<?php

namespace PangzLab\DMSMonitoring\Persistence;

class SqliteDatabase
{
    private $db;
    private $dbPath;

    public function __construct(string $dbFilePath)
    {
        if(!\file_exists($dbFilePath)) {
            throw new \Exception("Cannot connect to sqlite database! \n[Path]". $dbFilePath);
        }
        $this->dbPath = $dbFilePath;
    }

    public function connect(): object
    {
        $this->db = new \SQLite3($this->dbPath);
        return $this;
    }

    public function instance(): \SQLite3
    {
        return $this->db;
    }

    public function lastError(): string
    {
        return $this->lastError;
    }
}