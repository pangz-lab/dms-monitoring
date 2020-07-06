<?php

namespace PangzLab\DMSMonitoring\Application\Web\NotarizationDetailMatrix;

use PangzLab\DMSMonitoring\Domain\Service\NotaryCollection\NotaryCollectionService;
use PangzLab\DMSMonitoring\Domain\Service\NotaryBatchDataDownload\NotaryBatchDataDownloadService;
use PangzLab\DMSMonitoring\Domain\Service\NotaryBatchDataDownload\NotaryBatchDataService;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;

class NotarizationDetailMatrix
{
    private $di;
    private $services;
    public function __construct(DependencyInjection $di)
    {
        $this->di = $di;
        $this->services["collectionService"] = new NotaryCollectionService($this->di);
    }

    public function execute($params)
    {
        $view = \dirname(__FILE__)."/views/";
        $content = \file_get_contents($view."index.html");
        print $content;
    }
}