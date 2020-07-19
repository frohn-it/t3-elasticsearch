<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Mapping\Mapping;

interface MappingPreJsonSerializeHookInterface
{

    /**
     * @param array   $configuration
     * @param Mapping $mapping
     */
    public function preJsonSerialize(array $configuration, Mapping $mapping): void;
}