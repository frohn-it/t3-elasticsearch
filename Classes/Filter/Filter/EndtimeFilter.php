<?php


namespace BeFlo\T3Elasticsearch\Filter\Filter;


use BeFlo\T3Elasticsearch\Filter\AbstractFilter;
use BeFlo\T3Elasticsearch\Filter\FilterInterface;

class EndtimeFilter extends AbstractFilter
{
    public function getQueryFilterPart(): array
    {
        $fieldName = $this->configuration['field'] ?? 'endtime';
        return [
            'bool' => [
                'should' => [
                    [
                        'bool' => [
                            'must' => [
                                [
                                    'term' => [
                                        $fieldName => 0
                                    ]
                                ]
                            ]
                        ],
                    ],
                    [
                        'bool' => [
                            'must' => [
                                [
                                    'range' => [
                                        $fieldName => [
                                            'gte' => time()
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }


}