<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Mapping\Mapping;

interface PreProcessMappingHookInterface
{

    /**
     * @param array   $mappingConfiguration
     * @param Mapping $mapping
     */
    public function prepProcessMapping(array &$mappingConfiguration, Mapping $mapping): void;
}