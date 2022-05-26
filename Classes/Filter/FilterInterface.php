<?php


namespace BeFlo\T3Elasticsearch\Filter;


interface FilterInterface
{

    /**
     * @param array $configuration
     */
    public function setConfiguration(array $configuration): void;
    /**
     * @return array
     */
    public function getQueryFilterPart(): array;
}