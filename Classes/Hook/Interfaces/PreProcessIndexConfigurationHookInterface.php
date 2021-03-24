<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Index\Index;

interface PreProcessIndexConfigurationHookInterface extends BaseHookInterface
{

    /**
     * @param array $indexConfiguration
     * @param Index $index
     */
    public function preProcessIndexConfiguration(array &$indexConfiguration, Index $index): void;
}