<?php
namespace PangzLab\DMSMonitoring\Domain\Service\NotaryBatchDataDownload;

use PangzLab\DMSMonitoring\Domain\Common\Model\DownloadStatus;
use PangzLab\DMSMonitoring\Domain\Model\NotaryBatchDataDownload\NotaryBatchData;
use PangzLab\DMSMonitoring\Domain\Repo\NotaryBatchDataDownload\NotaryBatchDataRepo;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;
use PangzLab\DMSMonitoring\Config\ApiSetting;

class NotaryBatchDataService
{
    private $repo;
    const DOWNLOAD_PATH     = ApiSetting::NOTARY_DOWNLOAD_PATH.SEP;
    const UNKNOWN_LAST_PAGE = 0;

    public function __construct(DependencyInjection $di)
    {
        $this->repo = new NotaryBatchDataRepo($di);
    }

    public function getAll(): \Iterator
    {
        return $this->repo->getAll();
    }
    
    public function getAllForProcessing(): \Iterator
    {
        return $this->repo->getAllForProcessing();
    }

    public function setCompletedStatus(int $id): bool
    {
        return $this->repo->setStatus(DownloadStatus::COMPLETED, $id);
    }
}