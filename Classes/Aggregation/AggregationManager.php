<?php


namespace BeFlo\T3Elasticsearch\Aggregation;


class AggregationManager
{
    /**
     * @var AggregationInterface[]
     */
    private $aggregations = [];

    /**
     * AggregationManager constructor.
     * @param $aggregations
     */
    public function __construct($aggregations)
    {
        foreach ($aggregations as $aggregation) {
            $this->aggregations[get_class($aggregation)] = $aggregation;
        }
    }

    /**
     * @param array $aggregationConfigurations
     * @return array
     */
    public function getAggregations(array $aggregationConfigurations): array
    {
        return $this->getMethodFromAggregations($aggregationConfigurations, 'getAggregationPartForQuery');
    }

    /**
     * @param array $aggregationConfigurations
     * @param string $method
     * @return array
     */
    private function getMethodFromAggregations(array $aggregationConfigurations, string $method)
    {
        $result = [];
        foreach ($aggregationConfigurations as $identifier => $aggregationConfiguration) {
            if (!empty($this->aggregations[$aggregationConfiguration['aggregation']])) {
                $part = $this->aggregations[$aggregationConfiguration['aggregation']]->{$method}($aggregationConfiguration);
                if (!empty($part)) {
                    $result[md5($identifier)] = $part;
                }
            }
        }

        return $result;
    }

    /**
     * @param array $aggregationConfigurations
     * @return array
     */
    public function getFilter(array $aggregationConfigurations): array
    {
        return array_values($this->getMethodFromAggregations($aggregationConfigurations, 'getFilterPartForQuery'));
    }
}