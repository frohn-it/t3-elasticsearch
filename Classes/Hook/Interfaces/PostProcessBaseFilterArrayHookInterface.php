<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Filter\FilterInterface;

interface PostProcessBaseFilterArrayHookInterface extends BaseHookInterface
{

    /**
     * @param FilterInterface[] $baseFilter
     */
    public function postProcessBaseFilterArray(array &$baseFilter): void;
}