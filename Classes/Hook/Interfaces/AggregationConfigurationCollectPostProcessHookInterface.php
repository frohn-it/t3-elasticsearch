<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


interface AggregationConfigurationCollectPostProcessHookInterface extends BaseHookInterface
{

    /**
     * @param array $configurations
     */
    public function postProcessAggregationConfigurations(array &$configurations): void;
}