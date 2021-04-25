<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Indexer\PageIndexer;

interface PreProcessPageIndexDataHookInterface extends BaseHookInterface
{

    /**
     * @param array $pageIndexData
     * @param PageIndexer $indexer
     */
    public function preProcessPageIndexData(array &$pageIndexData, PageIndexer $indexer): void;
}