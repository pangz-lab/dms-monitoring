<?php
namespace PangzLab\DMSMonitoring\Infra;

abstract class Model
{
    abstract public function toArrayValue(): array;
    
    protected function call(string $property, $param, $props)
    {
        if(!isset($props[$property])) {
            throw new \Exception("[Unknown Property] Name: $property");
        }
        return $props[$property];
    }
}