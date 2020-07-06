<?php
namespace PangzLab\DMSMonitoring\Domain\Service\NotaryCollection;

use PangzLab\DMSMonitoring\Domain\Repo\NotaryCollection\NotaryHeaderRepo;
use PangzLab\DMSMonitoring\Domain\Repo\NotaryCollection\NotaryDetailRepo;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;
use PangzLab\DMSMonitoring\Config\ApiSetting;

class NotaryCollectionService
{
    private $headerRepo;
    private $detailRepo;

    function __construct(DependencyInjection $di)
    {
        $this->headerRepo = new NotaryHeaderRepo($di);
        $this->detailRepo = new NotaryDetailRepo($di);
    }

    public function getCustomRows(array $params): array {
        $columns = $params["COLUMNS"] ?? array(
            "txid",
            "chain",
            "block_height",
            "block_time",
            "block_datetime",
            "block_hash",
        );
        $limit     = $params["LIMIT"] ?? array(0, 10);
        $condition = $params["CONDITION"] ?? "";
        $orderBy   = $params["ORDER_BY"] ?? array(
            "columnIndex" => 3,
            "direction" => "desc",
        );
        $orderBy["direction"] = in_array(
            $orderBy["direction"], ["desc", "asc"]
        )? $orderBy["direction"] : "desc";
        
        return  $this->headerRepo->getCustomRows($limit, $columns, $orderBy, $condition);
    }

    public function getRowCount(string $condition = ""): int
    {
        return  $this->headerRepo->getRowCount($condition);
    }

    public function collect(array $notarizedCollection): bool
    {
        if(!isset($notarizedCollection["results"])) { return false; }

        $successfulInsert = false;
        $headerCollection = [];
        $detailCollection = [];
        $detailEntries    = [];

        try {
            $headerCollection = $this->prepareHeaderData($notarizedCollection["results"]);

            foreach($headerCollection as $key => $notaryHeader) {
                $successfulInsert = $this->headerRepo->save($notaryHeader);

                if($successfulInsert && $this->headerRepo->lastInsertId() > 0) {
                    $detailCollection = $this->prepareDetailData(
                        $notarizedCollection["results"][$key]["notaries"],
                        $notaryHeader->txId()
                    );
                    foreach($detailCollection as $notaryDetail) {
                        $this->detailRepo->save($notaryDetail);
                    }
                }
            }
            return true;
            
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function collectUntilLastSaved(array $notarizedCollection): bool
    {
        if(!isset($notarizedCollection["results"])) { return false; }

        $successfulInsert = false;
        $headerCollection = [];
        $detailCollection = [];
        $detailEntries    = [];
            
        try {
            $headerCollection = $this->prepareHeaderData($notarizedCollection["results"]);

            foreach($headerCollection as $key => $notaryHeader) {
                if($this->headerRepo->headerExists($notaryHeader->txId())) {
                    return false;
                }
                $successfulInsert = $this->headerRepo->save($notaryHeader);
                if($successfulInsert && $this->headerRepo->lastInsertId() > 0) {
                    $detailCollection = $this->prepareDetailData(
                        $notarizedCollection["results"][$key]["notaries"],
                        $notaryHeader->txId()
                    );
                    foreach($detailCollection as $notaryDetail) {
                        $this->detailRepo->save($notaryDetail);
                    }
                }
            }
            return true;
            
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function prepareHeaderData(array $data): \Iterator
    {
        return $this->headerRepo->prepareData($data);
    }

    private function prepareDetailData(array $data, string $headerId): \Iterator
    {
        return $this->detailRepo->prepareData($data, $headerId);
    }
}