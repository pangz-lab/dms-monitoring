<?php
namespace PangzLab\DMSMonitoring\Domain\Service\NotaryBatchDataDownload;

use PangzLab\DMSMonitoring\Domain\Common\Model\DownloadStatus;
use PangzLab\DMSMonitoring\Domain\Model\NotaryBatchDataDownload\NotaryBatchData;
use PangzLab\DMSMonitoring\Domain\Repo\NotaryBatchDataDownload\NotaryBatchDataDownloadRepo;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;
use PangzLab\DMSMonitoring\Config\ApiSetting;

class NotaryBatchDataDownloadService
{
    private $repo;

    public function __construct(DependencyInjection $di)
    {
        $this->repo = new NotaryBatchDataDownloadRepo($di);
    }

    public function extractDataFile(string $fileName): array
    {
        try {
            return $this->repo->extractDataFile(
                new NotaryBatchData(["batch_name" => $fileName])
            );
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function downloadPage(int $pageNumber): bool
    {
        try {
            return $this->prepareDownload($pageNumber)->download();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
        return true;
    }

    public function downloadUnSavedPageRow(int $pageNumber): array
    {
        try {
            return $this->prepareDownload($pageNumber)->downloadUnSavedPageRows();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function saveInfo(): bool
    {
        try {
            $this->repo->saveInfo();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }

        return true;
    }

    public function saveLastNotaryRow(string $headerTxId): void
    {
        $this->repo->saveToSession(
            NotaryBatchDataDownloadRepo::LAST_SAVE_ROW_KEY,
            $headerTxId
        );
    }

    public function downloadPageInRange(
        int $start,
        int $end = NotaryBatchDataDownloadRepo::UNKNOWN_LAST_PAGE): bool
    {
        if(
            $end != NotaryBatchDataDownloadRepo::UNKNOWN_LAST_PAGE && 
            ($start > $end || $start <= 0)
        ) { return false;}

        try {
            if($end == NotaryBatchDataDownloadRepo::UNKNOWN_LAST_PAGE) {
                $this->downloadUntilLastPage($start);
            } else {
                $this->downloadWithinPages($start, $end);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }

        return true;
    }

    public function downloadUntilLastUnSavedPageRow($pageCallback): void
    {
        $currentPage             = 1;
        $lastUnsavedRowExtracted = 1;
        $moveToNextPage          = true;
        do {
            $lastUnsavedRowExtracted = $this->downloadUnSavedPageRow($currentPage);
            $moveToNextPage = $pageCallback($lastUnsavedRowExtracted);
            $currentPage++;
        } while($moveToNextPage);
    }

    private function downloadUntilLastPage(int $start): void
    {
        $currentPage = $start;
        while(true) {
            if(!$this->downloadPage($currentPage)) {
                break;
            }
            $this->saveInfo();
            $currentPage++;
        }
    }

    private function downloadWithinPages(int $start, int $end): void
    {
        for($currentPage = $start; $currentPage <= $end; $currentPage++) {
            $this->downloadPage($currentPage);
            if($this->repo->status() == DownloadStatus::PARSING_FAILED) {
                break;
            }
            $this->saveInfo();
        }
    }

    private function prepareDownload(int $pageNumber): NotaryBatchDataDownloadRepo
    {
        $ext = NotaryBatchDataDownloadRepo::DATA_FILE_EXT;
        $fileName = md5(microtime()).".".$ext;
        $filePath = NotaryBatchDataDownloadRepo::DOWNLOAD_BASE_PATH.$fileName;
        return $this->repo
            ->setDownloadUri(ApiSetting::getUri($pageNumber))
            ->setDownloadFilePath($filePath)
            ->setPageNumber($pageNumber);
    }
}