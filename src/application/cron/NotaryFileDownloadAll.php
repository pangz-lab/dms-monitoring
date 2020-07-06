<?php

namespace PangzLab\DMSMonitoring\Application\Cron;

use PangzLab\DMSMonitoring\Domain\Service\NotaryBatchDataDownload\NotaryBatchDataDownloadService;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;

class NotaryFileDownloadAll
{
    private $di;
    public function __construct(DependencyInjection $di)
    {
        $this->di = $di;
    }

    public function execute()
    {
        $bddService = new NotaryBatchDataDownloadService($this->di);
        $bddService->downloadPageInRange(1);
    }
}