<?php
namespace PangzLab\DMSMonitoring\Domain\Repo\NotaryCollection;

use PangzLab\DMSMonitoring\Domain\Model\NotaryCollection\NotaryHeader;
use PangzLab\DMSMonitoring\Persistence\SqliteDatabaseOperation;
use PangzLab\DMSMonitoring\Persistence\DatabaseParameter;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;

class NotaryHeaderRepo
{
    private $dbOperation;
    public function __construct(DependencyInjection $di)
    {
        $this->dbOperation = $di->get("SqliteDbOperation");
    }

    public function getCustomRows(
        array $limit = [],
        array $columns = array(
            "txid",
            "chain",
            "block_height",
            "block_time",
            "block_datetime",
            "block_hash",
        ),
        array $orderBy = array(
            "columnIndex" => 3,
            "direction" => "desc",
        ),
        string $condition = ""
    ): array {
        $orderByColumns = $columns;
        $orderByIndex = $orderBy["columnIndex"] ?? 3;
        $orderByType  = $orderBy["direction"] ?? "desc";

        try {
            $dbParam = new DatabaseParameter([
                "table" => "notary_header",
                "rowLimit" => $limit,
                "columns" => $columns,
                "orderByColumns" => [$orderByColumns[$orderByIndex]],
                "orderBy" => $orderByType,
                "condition" => $condition,
            ]);
            return $this->dbOperation->select($dbParam, SQLITE3_NUM);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getRowCount(string $condition = ""): int
    {
        try {
            $dbParam = new DatabaseParameter([
                "table" => "notary_header",
                "condition" => $condition,
            ]);
            return $this->dbOperation->selectCount($dbParam);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function save(NotaryHeader $notaryHeader): bool
    {
        try {
            $dbParam = new DatabaseParameter([
                "table" => "notary_header",
                "columnValue" => $notaryHeader->toArrayValue()
            ]);

            return $this->dbOperation->insert($dbParam, false, true);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function headerExists(string $headerTxid): bool
    {
        try {
            $dbParam = new DatabaseParameter([
                "table" => "notary_header",
                "columnValue" => [
                    "txid" => $headerTxid,
                ],
                "condition" => "txid = :txid"
            ]);
            $result = $this->dbOperation->select($dbParam);
            if(!empty($result)) {
                return true;
            }
            return false;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function lastInsertId(): int
    {
        return $this->dbOperation->lastInsertId();
    }

    public function prepareData(array $data): \Iterator
    {
        return new class($data) implements \Iterator {
            private $position = 0;
            private $data;

            public function __construct(array $data) {
                $this->position = 0;
                $this->data     = $data;
            }

            public function rewind() {
                $this->position = 0;
            }

            public function current() {
                return new NotaryHeader($this->data[$this->position]);
            }

            public function key() {
                return $this->position;
            }

            public function next() {
                ++$this->position;
            }

            public function valid() {
                return isset($this->data[$this->position]);
            }
        };
    }
}