<?php


namespace BeFlo\T3Elasticsearch\Index;


use BeFlo\T3Elasticsearch\Hook\Interfaces\IndexLoaderPreAddHookInterface;
use BeFlo\T3Elasticsearch\Utility\HookTrait;
use BeFlo\T3Elasticsearch\Utility\JsonFileLoaderTrait;
use BeFlo\T3Elasticsearch\Utility\ObjectStorage;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IndexLoader implements SingletonInterface
{
    use HookTrait;
    use JsonFileLoaderTrait;

    /**
     * @var Index[]|ObjectStorage
     */
    protected $availableIndexes;

    /**
     * @var string
     */
    protected $configurationStoragePath = 'EXT:%s/Configuration/ElasticSearch/Index/';

    /**
     * IndexLoader constructor.
     */
    public function __construct()
    {
        $this->availableIndexes = new ObjectStorage();
        $this->loadAvailableIndexes();
    }

    /**
     * @param bool $force
     *
     * @return ObjectStorage|Index[]
     */
    public function loadAvailableIndexes(bool $force = false): ObjectStorage
    {
        if ($this->availableIndexes->count() === 0 || $force === true) {
            $this->loadIndexesByConfiguration();
            $this->loadIndexesFromDatabase();
        }

        return $this->availableIndexes;
    }

    /**
     * Load all indexes from the configuration path
     */
    protected function loadIndexesByConfiguration(): void
    {
        $extensions = ExtensionManagementUtility::getLoadedExtensionListArray();
        foreach ($extensions as $extension) {
            $this->checkExtensionForIndexConfigurationFiles($extension);
        }
    }

    /**
     * @param string $extensionName
     */
    protected function checkExtensionForIndexConfigurationFiles(string $extensionName): void
    {
        $fullPath = GeneralUtility::getFileAbsFileName(sprintf($this->configurationStoragePath, $extensionName));
        if (is_dir($fullPath)) {
            if ($handle = opendir($fullPath)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != ".." && substr($entry, -5) === '.json') {
                        $indexConfiguration = $this->loadJsonFile($fullPath . DIRECTORY_SEPARATOR . $entry);
                        $this->parseIndexConfiguration(lcfirst(basename($entry, '.json')), $indexConfiguration);
                    }
                }
                closedir($handle);
            }
        }
    }

    /**
     * @param string $identifier
     * @param array  $configuration
     */
    protected function parseIndexConfiguration(string $identifier, array $configuration): void
    {
        $parameter = [&$configuration, $this];
        $this->executeHook(IndexLoaderPreAddHookInterface::class, $parameter);

        $this->availableIndexes->attach(new Index($identifier, $configuration));
    }

    /**
     * Load all index from database records
     */
    protected function loadIndexesFromDatabase(): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_t3elasticsearch_index');
        $qb = $connection->createQueryBuilder();
        $rows = $qb->select('*')
            ->from('tx_t3elasticsearch_index')
            ->execute()->fetchAll();
        foreach ($rows ?? [] as $row) {
            $this->parseIndexConfiguration($row['identifier'], $row['configuration']);
        }
    }

    /**
     * @param string $identifier
     *
     * @return Index|null
     */
    public function getIndex(string $identifier): ?Index
    {
        return $this->availableIndexes->find($identifier);
    }
}