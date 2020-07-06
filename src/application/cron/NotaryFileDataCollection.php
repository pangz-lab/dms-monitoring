<?php

namespace PangzLab\DMSMonitoring\Application\Cron;

use PangzLab\DMSMonitoring\Domain\Service\NotaryCollection\NotaryCollectionService;
use PangzLab\DMSMonitoring\Domain\Service\NotaryBatchDataDownload\NotaryBatchDataDownloadService;
use PangzLab\DMSMonitoring\Domain\Service\NotaryBatchDataDownload\NotaryBatchDataService;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;

class NotaryFileDataCollection
{
    private $di;
    private $services;
    public function __construct(DependencyInjection $di)
    {
        $this->di = $di;
        $this->services["batchDataDLService"] = new NotaryBatchDataDownloadService($this->di);
        $this->services["batchDataService"]   = new NotaryBatchDataService($this->di);
        $this->services["collectionService"]  = new NotaryCollectionService($this->di);
    }

    public function execute()
    {
        $data = $this->services["batchDataService"]->getAllForProcessing();
        foreach($data as $key => $currentBatch) {
            $fileData = $this->services["batchDataDLService"]->extractDataFile($currentBatch->batchName());
            $this->services["collectionService"]->collect($fileData);
            $this->services["batchDataService"]->setCompletedStatus($currentBatch->id());
        }
    }

    private function saveLastRow(string $headerTxId): void
    {
        $this->services["batchDataDLService"]->saveLastNotaryRow($headerTxId);
    }
}