<?php

namespace PangzLab\DMSMonitoring\Application\API\NotarizationDetail;

use PangzLab\DMSMonitoring\Domain\Service\NotaryCollection\NotaryCollectionService;
use PangzLab\DMSMonitoring\Domain\Service\NotaryBatchDataDownload\NotaryBatchDataDownloadService;
use PangzLab\DMSMonitoring\Domain\Service\NotaryBatchDataDownload\NotaryBatchDataService;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;

class NotarizationDetail
{
    private $di;
    private $services;
    private $browserParams;
    private $columns = [
        "txid",
        "chain",
        "block_height",
        "block_time",
        "block_datetime",
        "block_hash",
    ];
    public function __construct(DependencyInjection $di)
    {
        $this->di = $di;
        $this->services["collectionService"]  = new NotaryCollectionService($this->di);
        $this->browserParams = $_GET;
    }

    public function execute($params)
    {
        $userParam     = $this->browserParams;
        $draw          = (int)$userParam["draw"]   ?? 1;
        $start         = (int)$userParam["start"]  ?? 0;
        $length        = (int)$userParam["length"] ?? 10;
        $orderByColumn = (int)$userParam["order"][0]["column"] ?? 3;
        $orderBy       = $userParam["order"][0]["dir"] ?? "desc";
        $params = [
            "LIMIT"     => [$start, $length],
            "COLUMNS"   => $this->columns,
            "CONDITION" => $this->prepareSearchCondition(),
            "ORDER_BY"  => [
                "columnIndex" => $orderByColumn,
                "direction" =>  $orderBy
            ],
        ];
        $result     = $this->services["collectionService"]->getCustomRows($params);
        $totalCount = $this->services["collectionService"]->getRowCount($params["CONDITION"]);
        $count  = count($result);
        $data   = [
            "draw" => $draw,
            "recordsTotal" => $count,
            "recordsFiltered" => $totalCount,
            "data" => $result
        ];
        print json_encode($data);
    }

    public function prepareSearchCondition(): string
    {
        $global    = $this->preparePerColumnsSearchCondition();
        $perColumn = $this->prepareGlobalSearchCondition();
        if(!empty($global) && !empty($perColumn)) {
            return "(".$global.") AND ".$perColumn;
        }

        if(!empty($global)) {
            return $global;
        }

        return $perColumn;
    }

    private function preparePerColumnsSearchCondition(): string
    {
        $searchCondition = [];
        $userParam       = $this->browserParams;
        $count           = count($this->columns);
        $columnValue = "";
        for($searchKey = 0; $searchKey < $count; $searchKey++) {
            $columnValue = trim($userParam["columns"][$searchKey]["search"]["value"]);
            if(!empty($columnValue)) {
                $searchCondition[] = $this->columns[$searchKey]." LIKE '%$columnValue%'";
            }
        }

        if(empty($searchCondition)) {
            return "";
        }

        return implode(" AND ", $searchCondition);
    }
    
    private function prepareGlobalSearchCondition(): string
    {
        $condition = [];
        $searchKey = trim($this->browserParams["search"]["value"]);
        if($searchKey == "") { return ""; }

        foreach($this->columns as $name) {
            $condition[] = $name. " LIKE '%$searchKey%'";
        }

        return implode(" OR ", $condition);
    }
}