<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


interface PostProcessFilterHookInterface extends BaseHookInterface
{

    /**
     * @param array $filter
     */
    public function postProcessFilter(array &$filter): void;
}