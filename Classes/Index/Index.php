<?php


namespace BeFlo\T3Elasticsearch\Index;


use BeFlo\T3Elasticsearch\Domain\Dto\IndexData;
use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PostProcessIndexingHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PostProcessRuntimeIndexingHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PreProcessIndexingHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PreProcessRuntimeIndexingHookInterface;
use BeFlo\T3Elasticsearch\Mapping\Mapping;
use BeFlo\T3Elasticsearch\Registry\IndexerRegistry;
use BeFlo\T3Elasticsearch\Utility\HookTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Index
{
    use HookTrait;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var Server
     */
    protected $server;

    /**
     * Index constructor.
     *
     * @param string $identifier
     * @param array  $configuration
     */
    public function __construct(string $identifier, array $configuration)
    {
        $this->identifier = $identifier;
        $this->configuration = $configuration;
        $this->initHooks(Index::class);
    }

    public function create()
    {

    }

    public function purge()
    {

    }

    public function updateMapping()
    {

    }

    public function isMappingDirty(): bool
    {

        return $this->getMapping()->isDirty([]);
    }

    public function delete()
    {

    }

    /**
     * Index a whole data set
     */
    public function index(): void
    {
        $parameter = [$this];
        $this->executeHook(PreProcessIndexingHookInterface::class, $parameter);
        foreach ($this->configuration['config']['indexer'] ?? [] as $indexerIdentifier) {
            $indexer = IndexerRegistry::getIndexer($indexerIdentifier);
            if (!empty($indexer)) {
                $indexer->setIndex($this)->index();
            }
        }
        $this->executeHook(PostProcessIndexingHookInterface::class, $parameter);
    }

    /**
     * @param IndexData $indexData
     */
    public function runtimeIndex(IndexData $indexData): void
    {
        $parameter = [$indexData, $this];
        $this->executeHook(PreProcessRuntimeIndexingHookInterface::class, $parameter);
        foreach ($this->configuration['config']['indexer'] ?? [] as $indexerIdentifier) {
            $indexer = IndexerRegistry::getRuntimeIndexer($indexerIdentifier);
            if (!empty($indexer)) {
                $indexer->setIndex($this)->index($indexData);
            }
        }
        $this->executeHook(PostProcessRuntimeIndexingHookInterface::class, $parameter);
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * @param Server $server
     *
     * @return Index
     */
    public function setServer(Server $server): Index
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return Mapping
     */
    protected function getMapping(): Mapping
    {
        return GeneralUtility::makeInstance(Mapping::class, $this->configuration['mapping'] ?? []);
    }

}