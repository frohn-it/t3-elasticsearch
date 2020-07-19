<?php


namespace BeFlo\T3Elasticsearch\Indexer;


use BeFlo\T3Elasticsearch\Domain\Dto\IndexData;
use BeFlo\T3Elasticsearch\Index\Index;

class PageIndexer implements RuntimeIndexerInterface
{
    /**
     * The identifier under which the indexer could be accessed through \BeFlo\T3Elasticsearch\Registry\IndexerRegistry
     */
    const IDENTIFIER = 't3_elasticsearch_page_indexer';

    /**
     * @return string
     */
    public static function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    /**
     * @param IndexData|null $data
     */
    public function index(IndexData $data = null): void
    {
        // TODO: Implement index() method.
    }

    /**
     * @param Index $index
     *
     * @return IndexerInterface
     */
    public function setIndex(Index $index): IndexerInterface
    {

        return $this;
    }

}