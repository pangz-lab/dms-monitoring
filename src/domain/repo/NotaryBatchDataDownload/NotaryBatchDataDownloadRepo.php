<?php
namespace PangzLab\DMSMonitoring\Domain\Repo\NotaryBatchDataDownload;

use PangzLab\DMSMonitoring\Domain\Common\Model\DownloadStatus;
use PangzLab\DMSMonitoring\Domain\Model\NotaryBatchDataDownload\NotaryBatchData;
use PangzLab\DMSMonitoring\Persistence\DatabaseParameter;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;
use PangzLab\DMSMonitoring\Config\ApiSetting;

class NotaryBatchDataDownloadRepo
{
    private $dbOperation;
    private $downloadUri;
    private $downloadFilePath;
    private $batchData;
    private $pageNumber;
    private $status;
    private $lastDownload;
    const LAST_SAVE_ROW_KEY       = "lastSavedRow";
    const RESULT_BATCH_DATA_COUNT = ApiSetting::NOTARY_BATCH_DATA_COUNT;
    const DOWNLOAD_BASE_PATH      = ApiSetting::NOTARY_DOWNLOAD_PATH.SEP;
    const DATA_FILE_EXT           = ApiSetting::NOTARY_DATA_FILE_EXT;
    const SESSION_FILE            = self::DOWNLOAD_BASE_PATH."session.json";
    const UNKNOWN_LAST_PAGE       = 0;
    const KEYS                    = [
        "RESULTS" => "results",
        "TXID"    => "txid",
        "COUNT"   => "count",
    ];
    function __construct(DependencyInjection $di)
    {
        $this->dbOperation = $di->get("SqliteDbOperation");
        $this->pageNumber  = 0;
    }

    public function setDownloadUri(string $uri): NotaryBatchDataDownloadRepo
    {
        $this->downloadUri = $uri;
        return $this;
    }

    public function setDownloadFilePath(string $path): NotaryBatchDataDownloadRepo
    {
        $this->downloadFilePath = $path;
        return $this;
    }

    public function setPageNumber(int $pageNumber): NotaryBatchDataDownloadRepo
    {
        $this->pageNumber = $pageNumber;
        return $this;
    }

    public function batchDataSummary(): string
    {
        return $this->batchData;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function download(): bool
    {
        try {
            $this->status = DownloadStatus::DOWNLOAD_INPROGRESS;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->downloadUri);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $this->batchData = curl_exec($ch);
            if($this->batchData === false) {
                throw new \Exception("[Download Failed] CURL error :".curl_error($ch));
            }
            curl_close($ch);
            
            $this->lastDownload = $this->getBatchDataSummary($this->batchData);
        } catch (\Exception $e) {
            $this->status = DownloadStatus::DOWNLOAD_FAILED;
            throw $e;
        }

        return true;
    }

    public function downloadUnSavedPageRows(): array
    {
        try {
            $this->status = DownloadStatus::DOWNLOAD_INPROGRESS;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->downloadUri);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $this->batchData = curl_exec($ch);
            
            if($this->batchData === false) {
                throw new \Exception("[Download Failed] CURL error :".curl_error($ch));
            }
            curl_close($ch);
            
            // $tempCollection  = $this->getUnCollectedRows();
            // print "Last saved data====================================\n";
            // print_r($tempCollection);
            $this->batchData = json_decode($this->batchData, true);
            return $this->batchData;
            
        } catch (\Exception $e) {
            $this->status = DownloadStatus::DOWNLOAD_FAILED;
            throw $e;
        }

        return 1;
    }

    public function saveInfo(): bool
    {
        try {
            $this->save($this->lastDownload);
            if($this->saveToFile()) {
                $this->resetDownload();
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
        return true;
    }

    public function extractDataFile(NotaryBatchData $batchData): array
    {
        $file = self::DOWNLOAD_BASE_PATH.$batchData->batchName();
        if(!file_exists($file)) { return [];}
        return \json_decode(file_get_contents($file), true);
    }

    public function getPageCount(int $totalData): int
    {
        return (
            ($totalData / self::RESULT_BATCH_DATA_COUNT) + 
            (($totalData % self::RESULT_BATCH_DATA_COUNT) > 0? 1: 0 )
        );
    }

    public function getPageFromSortIndex(int $sortIndex, int $totalData): int
    {
        return $this->getPageCount($totalData) + $sortIndex;
    }

    public function getSortIndexFromPage(int $page, int $totalData): int
    {
        $page--;
        return $this->getPageCount($totalData) - $page;
    }

    public function saveToSession(string $key, string $value): bool
    {
        if(!file_exists(self::SESSION_FILE)) {
            touch(self::SESSION_FILE);
        }
        $data       = $this->getSessionData();
        $data[$key] = $value;
        return file_put_contents(self::SESSION_FILE, json_encode($data)) > 0;
    }

    public function getSessionData(?string $key = null)
    {
        if(!file_exists(self::SESSION_FILE)) {
            touch(self::SESSION_FILE);
        }
        $data = json_decode(file_get_contents(self::SESSION_FILE), true);

        return is_null($key)? $data : $data[$key] ?? "";
    }

    // private function getUnCollectedRows(): array
    // {
    //     $lastSavedRow = $this->getSessionData(self::LAST_SAVE_ROW_KEY);
    //     $raw          = json_decode($this->batchDataSummary(), true);
    //     $result       = $raw[self::KEYS["RESULTS"]];
    //     $uniqueRows   = [];
    //     $savedRowFound = false;
    //     foreach($result as $currentRow) {
    //         if(trim($currentRow[self::KEYS["TXID"]]) == trim($lastSavedRow)) {
    //             $savedRowFound = true;
    //             break;
    //         }
    //         $uniqueRows[] = $currentRow;
    //     }

    //     $raw[self::KEYS["RESULTS"]] = $uniqueRows;
    //     return [
    //         "savedRowFound" => $savedRowFound, 
    //         "data" => $raw
    //     ];
    // }

    private function getBatchDataSummary(string $data): ?NotaryBatchData
    {
        $structuredRaw = json_decode($data, true);
        if(!isset($structuredRaw[self::KEYS["RESULTS"]])) {
            $this->status = DownloadStatus::PARSING_FAILED;
            throw new \Exception("[Parsing Error] Result not found!");
        }
        $results        = $structuredRaw[self::KEYS["RESULTS"]];
        $wholeDataCount = $structuredRaw[self::KEYS["COUNT"]];
        $batchRowCount  = count($results);
        $dateTimeNow    = strtotime("now");
        $sortSeq        = $this->getSortIndexFromPage($this->pageNumber, $wholeDataCount);
        $this->status   = DownloadStatus::DOWNLOAD_SUCCESSFUL;
        return new NotaryBatchData([
            "id" => null,
            "batch_name" => \basename($this->downloadFilePath),
            "start_txid" => $results[0][self::KEYS["TXID"]],
            "end_txid"   => $results[$batchRowCount-1][self::KEYS["TXID"]],
            "data_count" => $wholeDataCount,
            "batch_row_count" => $batchRowCount,
            "sort_seq"        => $sortSeq,
            "status"          => DownloadStatus::FOR_EXTRACTION,
            "created_date"    => $dateTimeNow,
            "updated_date"    => $dateTimeNow,
        ]);
    }

    private function saveToFile(): bool
    {
        touch($this->downloadFilePath);
        if(!file_exists($this->downloadFilePath)) {
            $this->status = DownloadStatus::DOWNLOAD_FILE_UNSAVED;
            return false;
        }
        $saved = (file_put_contents($this->downloadFilePath, $this->batchDataSummary()) > 0);

        if(!$saved) {
            $this->status = DownloadStatus::DOWNLOAD_FILE_UNSAVED;
            return false;
        }

        $this->status = DownloadStatus::DOWNLOAD_FILE_SAVED;
        return true;
    }

    private function resetDownload(): NotaryBatchDataDownloadRepo
    {
        $this->batchData = null;
        $this->status    = DownloadStatus::COMPLETED;
        return $this;
    }

    private function save(NotaryBatchData $batchData): bool
    {
        try {
            $dbParam = new DatabaseParameter([
                "table" => "notary_batch_data",
                "columnValue" => $batchData->toArrayValue()
            ]);
            $this->status = DownloadStatus::DOWNLOAD_DB_SAVED;
            return $this->dbOperation->insert($dbParam);
        } catch (\Exception $e) {
            $this->status = DownloadStatus::DOWNLOAD_DB_UNSAVED;
            throw $e;
        }
    }
}