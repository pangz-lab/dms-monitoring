<?php

namespace PangzLab\DMSMonitoring\Application\Cron;

use PangzLab\DMSMonitoring\Domain\Service\NotaryBatchDataDownload\NotaryBatchDataDownloadService;
use PangzLab\DMSMonitoring\Domain\Service\NotaryCollection\NotaryCollectionService;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;

class NotaryFileDownloadUnsavedPage
{
    private $di;
    public function __construct(DependencyInjection $di)
    {
        $this->di = $di;
        $this->services["batchDataDLService"] = new NotaryBatchDataDownloadService($this->di);
        $this->services["collectionService"]  = new NotaryCollectionService($this->di);
    }

    public function execute()
    {
        $processRows = function(array $fileData) {
            $this->services["collectionService"]->collectUntilLastSaved($fileData);
        };
        $this->services["batchDataDLService"]->downloadUntilLastUnSavedPageRow($processRows);
    }
}