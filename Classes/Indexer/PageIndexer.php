<?php


namespace BeFlo\T3Elasticsearch\Indexer;


class PageIndexer implements RuntimeIndexerInterface
{
    /**
     * The identifier under which the indexer could be accessed through \BeFlo\T3Elasticsearch\Registry\IndexerRegistry
     */
    const IDENTIFIER = 't3_elasticsearch_page_indexer';

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }
}