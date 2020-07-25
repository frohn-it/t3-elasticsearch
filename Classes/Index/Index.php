<?php


namespace BeFlo\T3Elasticsearch\Index;


use BeFlo\T3Elasticsearch\Domain\Dto\IndexData;
use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PostProcessIndexingHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PostProcessRuntimeIndexingHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PreProcessIndexConfigurationHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PreProcessIndexingHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PreProcessRuntimeIndexingHookInterface;
use BeFlo\T3Elasticsearch\Mapping\Mapping;
use BeFlo\T3Elasticsearch\Registry\IndexerRegistry;
use BeFlo\T3Elasticsearch\Server\Client;
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
     * @var \Elastica\Index
     */
    protected $elasticaIndex;

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

    /**
     * @param bool $reCreate
     */
    public function create(bool $reCreate = false)
    {
        $index = $this->getElasticaIndex();
        if ($index->exists() === false || $reCreate === true) {
            $indexConfiguration = [
                'number_of_shards'   => $this->configuration['config']['shards'],
                'number_of_replicas' => $this->configuration['config']['replicas'],
                'analysis'           => [
                    'analyzer' => $this->configuration['analyzer'],
                    'filter'   => $this->configuration['filter']
                ]
            ];
            $parameter = [&$indexConfiguration, $this];
            $this->executeHook(PreProcessIndexConfigurationHookInterface::class, $parameter);
            $index->create($indexConfiguration, $reCreate);
            $this->getMapping()->get()->send($index);
        }
    }

    /**
     * @return \Elastica\Index
     */
    protected function getElasticaIndex(): \Elastica\Index
    {
        if (empty($this->elasticaIndex)) {
            $this->elasticaIndex = Client::get($this->server)->getIndex($this->identifier);
        }

        return $this->elasticaIndex;
    }

    /**
     * @return Mapping
     */
    protected function getMapping(): Mapping
    {
        return GeneralUtility::makeInstance(Mapping::class, $this->configuration['mapping'] ?? []);
    }

    /**
     * Purge the index
     */
    public function purge(): void
    {
        $index = $this->getElasticaIndex();
        if ($index->exists()) {
            $index->flush();
            // @ToDo Handle response
        }
    }

    /**
     * @return bool
     */
    public function updateMapping(): bool
    {
        $result = false;
        $index = $this->getElasticaIndex();
        if ($index->exists()) {
            $this->getMapping()->get()->send($index);
            $result = true;
        }

        return $result;
    }

    public function isMappingDirty(): bool
    {

        return $this->getMapping()->isDirty([]);
    }

    /**
     * @return bool
     */
    public function exist(): bool
    {
        return $this->getElasticaIndex()->exists();
    }

    /**
     *
     */
    public function delete(): void
    {
        $this->getElasticaIndex()->delete();
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
}