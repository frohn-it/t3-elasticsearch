<?php


namespace BeFlo\T3Elasticsearch\Filter\Filter;


use BeFlo\T3Elasticsearch\Filter\AbstractFilter;
use BeFlo\T3Elasticsearch\Filter\FilterInterface;

class StarttimeFilter extends AbstractFilter
{
    public function getQueryFilterPart(): array
    {
        $fieldName = $this->configuration['field'] ?? 'starttime';
        return [
            'bool' => [
                'must' => [
                    [
                        'range' => [
                            $fieldName => [
                                'lte' => time()
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

}