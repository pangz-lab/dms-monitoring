<?php

use PangzLab\DMSMonitoring\Domain\NotaryCollection\Service\NotaryCollectionService;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;

class NotaryCollectionServiceTest
{

    public function collectionTest()
    {
        DependencyInjection::add("SqliteDbOperation");
        $notaryCollection = new NotaryCollectionService();
    }
}
