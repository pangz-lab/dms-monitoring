<?php
namespace PangzLab\DMSMonitoring\Domain\Repo\NotaryCollection;

use PangzLab\DMSMonitoring\Domain\Model\NotaryCollection\NotaryDetail;
use PangzLab\DMSMonitoring\Persistence\SqliteDatabaseOperation;
use PangzLab\DMSMonitoring\Persistence\DatabaseParameter;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;

class NotaryDetailRepo
{
    private $dbOperation;
    public function __construct(DependencyInjection $di)
    {
        $this->dbOperation = $di->get("SqliteDbOperation");
    }

    public function save(NotaryDetail $notaryDetail): bool
    {
        try {
            $dbParam = new DatabaseParameter([
                "table" => "notary_detail",
                "columnValue" => $notaryDetail->toArrayValue()
            ]);

            return $this->dbOperation->insert($dbParam, false, true);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function prepareData(array $data, string $notaryHeaderId): \Iterator
    {
        return new class($data, $notaryHeaderId) implements \Iterator {
            private $position = 0;
            private $headerId;
            private $data;

            public function __construct(array $data, string $headerId) {
                $this->position = 0;
                $this->data     = $data;
                $this->headerId = $headerId;
            }

            public function rewind() {
                $this->position = 0;
            }

            public function current() {
                return new NotaryDetail(
                    [
                        "id"        => null,
                        "detail_id" => $this->position,
                        "name"      => $this->data[$this->position],
                        "notary_header_txid" => $this->headerId,
                    ]
                );
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