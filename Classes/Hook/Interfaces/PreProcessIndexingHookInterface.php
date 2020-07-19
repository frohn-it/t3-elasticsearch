<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Index\Index;

interface PreProcessIndexingHookInterface
{

    /**
     * @param Index $index
     */
    public function preProcessIndexing(Index $index): void;
}