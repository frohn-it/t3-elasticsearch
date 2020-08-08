<?php


namespace BeFlo\T3Elasticsearch\Indexer;


use BeFlo\T3Elasticsearch\Domain\Dto\IndexData;
use BeFlo\T3Elasticsearch\Index\Index;
use Elastica\Document;

class PageIndexer implements RuntimeIndexerInterface
{
    /**
     * The identifier under which the indexer could be accessed through \BeFlo\T3Elasticsearch\Registry\IndexerRegistry
     */
    const IDENTIFIER = 't3_elasticsearch_page_indexer';

    /**
     * @var Index
     */
    protected $index;

    /**
     * @var IndexData
     */
    protected $indexData;

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
        $this->indexData = $data;
        $documentData = [
            'id' => $this->indexData->getTypoScriptFrontEndController()->page['uid'],
            'title' => $this->indexData->getTypoScriptFrontEndController()->page['title'],
        ];
        $document = new Document($documentData['uid'], $documentData);
        $this->index->getElasticaIndex()->addDocument($document);
    }

    /**
     * @param Index $index
     *
     * @return IndexerInterface
     */
    public function setIndex(Index $index): IndexerInterface
    {
        $this->index = $index;

        return $this;
    }

}