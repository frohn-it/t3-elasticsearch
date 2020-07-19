<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Index\IndexLoader;

interface IndexLoaderPreAddHookInterface
{
    /**
     * This hook will be executed before an index configuration will be assigned to the index object
     *
     * @param array       $indexConfiguration
     * @param IndexLoader $indexLoader
     */
    public function preAddIndex(array &$indexConfiguration, IndexLoader $indexLoader): void;
}