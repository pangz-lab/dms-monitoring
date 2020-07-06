<?php
namespace PangzLab\DMSMonitoring\Domain\Repo\NotaryBatchDataDownload;

use PangzLab\DMSMonitoring\Domain\Common\Model\DownloadStatus;
use PangzLab\DMSMonitoring\Domain\Model\NotaryBatchDataDownload\NotaryBatchData;
use PangzLab\DMSMonitoring\Persistence\DatabaseParameter;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;
use PangzLab\DMSMonitoring\Config\ApiSetting;

class NotaryBatchDataRepo
{
    function __construct(DependencyInjection $di)
    {
        $this->dbOperation = $di->get("SqliteDbOperation");
    }

    public function save(NotaryBatchData $batchData): bool
    {
        try {
            $dbParam = new DatabaseParameter([
                "table" => "notary_batch_data",
                "columnValue" => $batchData->toArrayValue()
            ]);
            return $this->dbOperation->insert($dbParam);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function setStatus(int $status, int $id): bool
    {
        try {
            $dbParam = new DatabaseParameter([
                "table" => "notary_batch_data",
                "columnValue" => [
                    "status" => $status,
                    "id"    => $id
                ],
                "condition" => "id = :id"
            ]);
            $dbParam->setColumns(["status = :status"]);
            return $this->dbOperation->update($dbParam);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAll(): \Iterator
    {
        try {
            $dbParam = new DatabaseParameter(["table" => "notary_batch_data"]);
            return $this->convertData($this->dbOperation->select($dbParam));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAllForProcessing(): \Iterator
    {
        try {
            $dbParam = new DatabaseParameter(
                [
                    "table" => "notary_batch_data",
                    "condition" => "status = ".DownloadStatus::FOR_EXTRACTION,
                ]
            );
            return $this->convertData($this->dbOperation->select($dbParam));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function convertData(array $data): \Iterator
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
                return new NotaryBatchData($this->data[$this->position]);
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