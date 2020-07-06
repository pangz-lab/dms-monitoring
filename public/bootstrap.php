<?php
define("APP_COLLECTION", [
    "cron" => [
        "notary_file_download_all"     => "PangzLab\DMSMonitoring\Application\Cron\NotaryFileDownloadAll",
        "notary_file_download_unsaved" => "PangzLab\DMSMonitoring\Application\Cron\NotaryFileDownloadUnsavedPage",
        "notary_file_data_collection"  => "PangzLab\DMSMonitoring\Application\Cron\NotaryFileDataCollection",
    ],
    "web" => [
        "/web/notary/detail" => "PangzLab\DMSMonitoring\Application\Web\NotarizationDetailMatrix\NotarizationDetailMatrix",
        "/api/notary/detail" => "PangzLab\DMSMonitoring\Application\API\NotarizationDetail\NotarizationDetail",
    ]
]);