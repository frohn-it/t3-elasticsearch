<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Index\Index;

interface PostRealIndexNameGenerationHookInterface
{

    /**
     * @param string $aliasName
     * @param array  $realIndexNames
     * @param Index  $index
     */
    public function postProcessRealIndexNames(string $aliasName, array &$realIndexNames, Index $index): void;
}