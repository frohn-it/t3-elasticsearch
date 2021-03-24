<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Index\Index;

interface PostProcessIndexingHookInterface extends BaseHookInterface
{

    /**
     * @param Index $index
     */
    public function postProcessIndexing(Index $index): void;
}