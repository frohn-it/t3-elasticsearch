<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Configuration\ConfigurationManager;

interface ConfigurationManagerCachePostProcessHookInterface
{

    /**
     * @param array                $configuration
     * @param ConfigurationManager $pObj
     */
    public function postProcessCacheConfiguration(array &$configuration, ConfigurationManager $pObj): void;
}