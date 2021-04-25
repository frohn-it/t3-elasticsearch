<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Service\SearchService;

interface PostProcessFinalSearchQueryHookInterface extends BaseHookInterface
{

    /**
     * @param array $searchQuery
     * @param string $path
     * @param string $method
     * @param SearchService $searchService
     */
    public function postProcessFinalSearchQuery(array &$searchQuery, string &$path, string &$method, SearchService $searchService): void;
}