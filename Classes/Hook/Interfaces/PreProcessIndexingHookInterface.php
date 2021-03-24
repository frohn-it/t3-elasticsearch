<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Index\Index;

interface PreProcessIndexingHookInterface extends BaseHookInterface
{

    /**
     * @param Index $index
     */
    public function preProcessIndexing(Index $index): void;
}