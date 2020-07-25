<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Index\Index;

interface PreProcessIndexConfigurationHookInterface
{

    /**
     * @param array $indexConfiguration
     * @param Index $index
     */
    public function preProcessIndexConfiguration(array &$indexConfiguration, Index $index): void;
}