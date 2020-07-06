<?php
namespace PangzLab\DMSMonitoring\Domain\Common\Model;

class DownloadStatus
{
    const COMPLETED = 0;
    const FOR_EXTRACTION      = 200;
    const DOWNLOAD_INPROGRESS = 1;
    const DOWNLOAD_SUCCESSFUL = 2;
    const DOWNLOAD_DB_SAVED   = 3;
    const DOWNLOAD_FILE_SAVED = 4;
    const PARSING_SUCCESSFUL  = 5;
    

    const DOWNLOAD_FAILED       = -2;
    const DOWNLOAD_DB_UNSAVED   = -3;
    const DOWNLOAD_FILE_UNSAVED = -4;
    const PARSING_FAILED        = -5;
}