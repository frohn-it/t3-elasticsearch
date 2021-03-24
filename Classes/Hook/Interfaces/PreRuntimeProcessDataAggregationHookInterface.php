<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Domain\Dto\IndexData;

interface PreRuntimeProcessDataAggregationHookInterface extends BaseHookInterface
{

    /**
     * @param IndexData $indexData
     */
    public function preProcessRuntimeIndexData(IndexData $indexData): void;
}