<?php


namespace BeFlo\T3Elasticsearch\Indexer;


use BeFlo\T3Elasticsearch\Domain\Dto\IndexData;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PreProcessPageIndexDataHookInterface;
use BeFlo\T3Elasticsearch\Index\Index;
use BeFlo\T3Elasticsearch\Utility\HookTrait;
use Elastica\Document;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class PageIndexer implements RuntimeIndexerInterface
{
    use HookTrait;

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
        if($this->isIndexNeeded($data->getTypoScriptFrontEndController()->page)) {
            $this->indexData = $data;
            $documentData = $this->getDocumentData($this->indexData->getTypoScriptFrontEndController()->page);
            $document = new Document($documentData['uid'], $documentData);
            $this->index->getElasticaIndex()->addDocument($document);
            $this->updatePageLastIndexDate($documentData['uid']);
        }
    }

    /**
     * @param int $pageUid
     */
    private function updatePageLastIndexDate(int $pageUid): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $connection->update('pages', ['tx_t3elasticsearch_last_indexed' => time()], ['uid' => $pageUid]);
    }

    /**
     * @param array $pageRecord
     * @return bool
     */
    private function isIndexNeeded(array $pageRecord): bool
    {
        if($pageRecord['tstamp'] > $pageRecord['tx_t3elasticsearch_last_indexed']) {
            $result = true;
        } else {
            $result = $this->checkIfRecordsOfPageAreChanged((int)$pageRecord['uid'], (int)$pageRecord['tx_t3elasticsearch_last_indexed']);
        }

        return $result;
    }

    /**
     * @param int $pageUid
     * @param int $lastIndexed
     * @return bool
     */
    private function checkIfRecordsOfPageAreChanged(int $pageUid, int $lastIndexed): bool
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');
        $qb = $connection->createQueryBuilder();
        $rowCount = $qb->select('*')
            ->from('tt_content')
            ->where(
                $qb->expr()->eq('pid', $pageUid),
                $qb->expr()->gt('tstamp', $lastIndexed)
            )->execute()->rowCount();

        return $rowCount > 0;
    }

    /**
     * @param array $page
     * @return array
     */
    private function getDocumentData(array $page): array
    {
        $result = [
            'uid' => $page['uid'],
            'title' => $page['seo_title'] ?: $page['title'],
            'description' => $page['seo_description'],
            'canonical' => $page['canonical'],
            'abstract' => $page['abstract'],
            'keywords' => GeneralUtility::trimExplode(',', $page['keywords']),
            'author' => $page['author'],
            'author_email' => $page['author_email'],
            'doktype' => $page['doktype'],
            'starttime' => $page['starttime'],
            'endtime' => $page['endtime'],
            'categories' => $this->getCategories($page['uid']),
            'content' => $this->getBodytext()
        ];
        $parameters = [&$result, $this];
        $this->executeHook(PreProcessPageIndexDataHookInterface::class, $parameters);

        return $result;
    }

    /**
     * @return string
     */
    private function getBodytext(): string
    {
        $result = '';
        $content = $this->getContentForReplacing();
        if(!empty($content)) {
            $processed = trim(preg_replace(['/<(style|script)[^>]*>(?<content>[^<]*)<\/(style|script)>/','/\s+/'],' ', $content));
            $result = trim(preg_replace(['/<[^>]*>/','/\s+/'],' ', $processed));
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getContentForReplacing(): string
    {
        $content = $this->indexData->getTypoScriptFrontEndController()->content;
        if(strpos($content, 'TYPO3SEARCH_begin') !== false) {
            preg_match("/<!--TYPO3SEARCH_begin-->(.*?)<!--TYPO3SEARCH_end-->/s", $this->indexData->getTypoScriptFrontEndController()->content, $matches);
        } else {
            preg_match("/<body>(.*?)<\/body>/s", $this->indexData->getTypoScriptFrontEndController()->content, $matches);
        }

        return $matches[1] ?? '';
    }

    /**
     * @param int $pageUid
     * @return array
     */
    private function getCategories(int $pageUid): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_category');
        $qb = $connection->createQueryBuilder();
        $rows = $qb->select('*')
            ->from('sys_category', 'c')
            ->innerJoin('c', 'sys_category_record_mm', 'mm', 'c.uid = mm.uid_local AND mm.tablenames = "pages" AND mm.fieldname = "categories"')
            ->where(
                $qb->expr()->eq('mm.uid_foreign', $pageUid)
            )->execute()->fetchAll();
        $result = [];
        foreach($rows ?? [] as $row) {
            $result[] = $row['title'];
        }

        return $result;
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