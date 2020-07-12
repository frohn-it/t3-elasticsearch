<?php


namespace BeFlo\T3Elasticsearch\Server;


use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Hook\Interfaces\ServerLoaderPreAddHookInterface;
use BeFlo\T3Elasticsearch\Utility\HookTrait;
use SplObjectStorage;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ServerLoader
{
    use HookTrait;

    /**
     * @var SplObjectStorage|Server[]
     */
    protected $server;

    /**
     * @var string
     */
    protected $serverFilePathTemplate = 'EXT:%s/Configuration/ElasticSearch/Server.json';

    /**
     * ServerLoader constructor.
     */
    public function __construct()
    {
        $this->server = new SplObjectStorage();
        $this->initHooks(ServerLoader::class);
    }

    /**
     * @return Server[]|SplObjectStorage
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
            foreach ($this->loadServerConfigurationFile($fullPath) as $identifier => $serverConfiguration) {
                $this->parseServerConfiguration($identifier, $serverConfiguration);
            }
        }
    }

    /**
     * @param string $filePath
     *
     * @return array
     */
    protected function loadServerConfigurationFile(string $filePath): array
    {
        $result = [];
        $content = @file_get_contents($filePath);
        if ($content !== false) {
            $data = @json_decode($content, true);
            if (is_array($data)) {
                $result = $data;
            }
        }

        return $result;
    }

    /**
     * @param string $identifier
     * @param array  $configuration
     */
    protected function parseServerConfiguration(string $identifier, array $configuration): void
    {
        if (!empty($configuration['host']) && !empty($configuration['port'])) {
            $server = new Server($identifier);
            $server->setHost($configuration['host']);
            $server->setPort($configuration['port']);
            $server->setIndexes($configuration['indexes'] ?? []);
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
            $this->parseServerConfiguration($serverConfiguration['identifier'], $serverConfiguration);
        }
    }
}