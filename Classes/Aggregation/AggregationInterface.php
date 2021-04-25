<?php


namespace BeFlo\T3Elasticsearch\Aggregation;


interface AggregationInterface
{

    /**
     * @param array $aggregationData
     * @return string
     */
    public function getRenderedAggregation(array $aggregationData): string;

    /**
     * @param array $configuration
     * @return array
     */
    public function getAggregationPartForQuery(array $configuration): array;

    /**
     * @param array $availableParameters
     * @return array
     */
    public function getFilterPartForQuery(array $availableParameters): array;
}