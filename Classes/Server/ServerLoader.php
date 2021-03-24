<?php


namespace BeFlo\T3Elasticsearch\Server;


use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Hook\Interfaces\ServerLoaderPreAddHookInterface;
use BeFlo\T3Elasticsearch\Index\Index;
use BeFlo\T3Elasticsearch\Index\IndexLoader;
use BeFlo\T3Elasticsearch\Utility\HookTrait;
use BeFlo\T3Elasticsearch\Utility\JsonFileLoaderTrait;
use BeFlo\T3Elasticsearch\Utility\ObjectStorage;
use SplObjectStorage;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ServerLoader
 *
 * @package BeFlo\T3Elasticsearch\Server
 *
 * @internal
 */
class ServerLoader implements SingletonInterface
{
    use HookTrait;
    use JsonFileLoaderTrait;

    /**
     * @var ObjectStorage|Server[]
     */
    protected $server;

    /**
     * @var string
     */
    protected $serverFilePathTemplate = 'EXT:%s/Configuration/ElasticSearch/Server.json';

    /**
     * @var IndexLoader
     */
    protected $indexLoader;

    /**
     * ServerLoader constructor.
     *
     * @param IndexLoader $indexLoader
     */
    public function __construct(IndexLoader $indexLoader)
    {
        $this->server = new ObjectStorage();
        $this->indexLoader = $indexLoader;
    }

    /**
     * @return Server[]|ObjectStorage
     */
    public function loadAvailableServer(): SplObjectStorage
    {
        $this->loadServerFromFiles();
        $this->loadServerFromDatabase();

        return $this->server;
    }

    /**
     * Load server from configuration files
     */
    protected function loadServerFromFiles()
    {
        $extensions = ExtensionManagementUtility::getLoadedExtensionListArray();
        foreach ($extensions as $extension) {
            $this->checkExtensionForServerConfigurationFile($extension);
        }
    }

    /**
     * @param string $extensionName
     */
    protected function checkExtensionForServerConfigurationFile(string $extensionName): void
    {
        $fullPath = GeneralUtility::getFileAbsFileName(sprintf($this->serverFilePathTemplate, $extensionName));
        if (file_exists($fullPath)) {
            foreach ($this->loadJsonFile($fullPath) as $identifier => $serverConfiguration) {
                $this->parseServerConfiguration($identifier, $serverConfiguration);
            }
        }
    }

    /**
     * @param string $identifier
     * @param array  $configuration
     */
    protected function parseServerConfiguration(string $identifier, array $configuration): void
    {
        if (!empty($configuration['host']) && !empty($configuration['port'])) {
            $server = GeneralUtility::makeInstance(Server::class, $identifier);
            $server->setHost($configuration['host']);
            $server->setPort($configuration['port']);
            foreach ($configuration['indexes'] ?? [] as $indexIdentifier) {
                $index = $this->indexLoader->getIndex($indexIdentifier);
                if ($index instanceof Index) {
                    $index->setServer($server);
                    $server->addIndex($index);
                }
            }
            $parameter = [$server, $configuration, $this];
            $this->executeHook(ServerLoaderPreAddHookInterface::class, $parameter);
            if (!$this->server->contains($server)) {
                $this->server->attach($server);
            }
        }
    }

    /**
     * Load the server configuration from the database
     */
    protected function loadServerFromDatabase(): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_t3elasticsearch_server');
        $qb = $connection->createQueryBuilder();
        $rows = $qb->select('*')
            ->from('tx_t3elasticsearch_server')
            ->execute()->fetchAll();
        foreach ($rows ?? [] as $serverConfiguration) {
            $serverConfiguration['indexes'] = $this->loadIndexIdentifierFromDatabaseForServer($serverConfiguration['indexes']);
            $this->parseServerConfiguration($serverConfiguration['identifier'], $serverConfiguration);
        }
    }

    /**
     * @param string $uidList
     *
     * @return array
     */
    protected function loadIndexIdentifierFromDatabaseForServer(string $uidList): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_t3elasticsearch_index');
        $qb = $connection->createQueryBuilder();
        $rows = $qb->select('identifier')
            ->from('tx_t3elasticsearch_index')
            ->where(
                $qb->expr()->in('uid', GeneralUtility::intExplode(',', $uidList, true))
            )->execute()->fetchAll();

        return $rows ?? [];
    }
}