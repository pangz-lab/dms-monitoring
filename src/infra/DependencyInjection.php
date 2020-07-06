<?php

namespace PangzLab\DMSMonitoring\Infra;

class DependencyInjection
{
    private $diObjects = [];

    public function __construct(array $objectList)
    {
        $this->diObjects = $objectList;
    }
    
    public function add(string $name, object $ob): void
    {
        $this->diObjects[$name] = $ob;
    }

    public function get(string $name): object
    {
        return $this->diObjects[$name];
    }
}