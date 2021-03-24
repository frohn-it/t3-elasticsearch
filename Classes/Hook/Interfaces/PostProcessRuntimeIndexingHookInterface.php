<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Domain\Dto\IndexData;
use BeFlo\T3Elasticsearch\Index\Index;

interface PostProcessRuntimeIndexingHookInterface extends BaseHookInterface
{
    /**
     * @param IndexData $indexData
     * @param Index     $index
     */
    public function postProcessRuntimeIndexing(IndexData $indexData, Index $index): void;
}