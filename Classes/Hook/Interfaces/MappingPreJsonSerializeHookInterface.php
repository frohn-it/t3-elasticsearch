<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Mapping\Mapping;

interface MappingPreJsonSerializeHookInterface extends BaseHookInterface
{

    /**
     * @param array   $configuration
     * @param Mapping $mapping
     */
    public function preJsonSerialize(array $configuration, Mapping $mapping): void;
}