<?php


namespace BeFlo\T3Elasticsearch\Indexer;


use BeFlo\T3Elasticsearch\Domain\Dto\IndexData;
use BeFlo\T3Elasticsearch\Index\Index;

interface IndexerInterface
{
    /**
     * @return string
     */
    public static function getIdentifier(): string;

    /**
     * @param IndexData|null $data
     */
    public function index(IndexData $data = null): void;

    /**
     * @param Index $index
     *
     * @return IndexerInterface
     */
    public function setIndex(Index $index): IndexerInterface;
}