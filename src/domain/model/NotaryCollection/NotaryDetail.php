<?php
namespace PangzLab\DMSMonitoring\Domain\Model\NotaryCollection;

use PangzLab\DMSMonitoring\Infra\Model;

class NotaryDetail extends Model
{
    private $id;
    private $detailId;
    private $name;
    private $notaryHeaderTxId;

    public function __construct(array $data)
    {
        $this->id       = $data["id"];
        $this->detailId = $data["detail_id"];
        $this->name     = $data["name"];
        $this->notaryHeaderTxId = $data["notary_header_txid"];
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