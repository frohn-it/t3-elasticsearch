<?php


namespace BeFlo\T3Elasticsearch\Aggregation\BasicAggregations;


use BeFlo\T3Elasticsearch\Aggregation\AbstractAggregation;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class SelectAggregation extends AbstractAggregation
{
    public function getRenderedAggregation(array $aggregationData): string
    {
        return '';
    }

    /**
     * @param array $configuration
     * @return array
     */
    public function getAggregationPartForQuery(array $configuration): array
    {
        return $configuration['config'] ?? [];
    }

    public function getFilterPartForQuery(array $availableParameters): array
    {
        $result = [];
        DebuggerUtility::var_dump($availableParameters);
        return $result;
    }


}