<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Mapping\Mapping;

interface PostProcessMappingHookInterface
{

    /**
     * @param array   $mappingConfiguration
     * @param Mapping $mapping
     */
    public function postProcessMapping(array &$mappingConfiguration, Mapping $mapping): void;
}