<?php
namespace PangzLab\DMSMonitoring\Domain\Model\NotaryCollection;

use PangzLab\DMSMonitoring\Infra\Model;

class NotaryHeader extends Model
{
    private $txId;
    private $chain;
    private $blockHeight;
    private $blockTime;
    private $blockDatetime;
    private $blockHash;
    private $acNtxBlockhash;
    private $acNtxHeight;
    private $opret;
    private $season;

    public function __construct(array $data)
    {
        $this->txId  = $data["txid"] ?? null;
        $this->chain = $data["chain"] ?? null;
        $this->blockHeight    = $data["block_height"] ?? null;
        $this->blockTime      = $data["block_time"] ?? null;
        $this->blockDatetime  = $data["block_datetime"] ?? null;
        $this->blockHash      = $data["block_hash"] ?? null;
        $this->acNtxBlockhash = $data["ac_ntx_blockhash"] ?? null;
        $this->acNtxHeight    = $data["ac_ntx_height"] ?? null;
        $this->opret  = $data["opret"] ?? null;
        $this->season = $data["season"] ?? null;
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