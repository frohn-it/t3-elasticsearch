<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Index\Index;

interface PostProcessIndexingHookInterface
{

    /**
     * @param Index $index
     */
    public function postProcessIndexing(Index $index): void;
}