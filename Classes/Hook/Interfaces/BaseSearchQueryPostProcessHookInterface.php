<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Service\SearchService;

interface BaseSearchQueryPostProcessHookInterface extends BaseHookInterface
{
    /**
     * @param array $baseSearchQuery
     * @param SearchService $searchService
     */
    public function postProcessBaseSearchQuery(array &$baseSearchQuery, SearchService $searchService): void;
}