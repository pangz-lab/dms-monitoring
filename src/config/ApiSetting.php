<?php
namespace PangzLab\DMSMonitoring\Config;

class ApiSetting
{
    const ASSET_DIR = BASE_DIR_SRC.SEP."assets";
    const DB_NAME = "dms_monitoring";
    const DB_PATH = BASE_DIR_SRC.SEP."db".SEP.self::DB_NAME;
    const NOTARY_SEASON           = "Season_4";
    const NOTARY_BATCH_DATA_COUNT = 100;
    const NOTARY_DATA_FILE_EXT    = "json";
    const NOTARY_DOWNLOAD_PATH    = self::ASSET_DIR.SEP."notarization_files";
    const NOTARY_API_URI = "http://notary.earth:8762/api/source/notarised/?page=%s&season=%s";

    public static function getUri(int $pageNumber = 0): string
    {
        return \sprintf(self::NOTARY_API_URI, $pageNumber, self::NOTARY_SEASON);
    }
}
