<?php
namespace PangzLab\DMSMonitoring\Domain\Model\NotaryBatchDataDownload;

use PangzLab\DMSMonitoring\Infra\Model;

class NotaryBatchData extends Model
{
    private $id;
    private $batchName;
    private $startTxId;
    private $endTxId;
    private $dataCount;
    private $batchRowCount;
    private $sortSeq;
    private $status;
    private $createdDate;
    private $updatedDate;

    public function __construct(array $data)
    {
        $this->id        = $data["id"] ?? null;
        $this->batchName = $data["batch_name"] ?? null;
        $this->startTxId = $data["start_txid"] ?? null;
        $this->endTxId   = $data["end_txid"] ?? null;
        $this->dataCount = $data["data_count"] ?? null;
        $this->batchRowCount = $data["batch_row_count"] ?? null;
        $this->sortSeq       = $data["sort_seq"] ?? null;
        $this->status        = $data["status"] ?? null;
        $this->createdDate   = $data["created_date"] ?? null;
        $this->updatedDate   = $data["updated_date"] ?? null;
    }

    public function toArrayValue(): array
    {
        return get_object_vars($this);
    }

    public function __call(string $property, $param) 
    {
        return $this->call($property, $param, get_object_vars($this));
    }
}