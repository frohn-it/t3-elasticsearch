<?php


namespace BeFlo\T3Elasticsearch\Indexer;


interface IndexerInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string;
}