<?php


namespace BeFlo\T3Elasticsearch\Index;


use BeFlo\T3Elasticsearch\Domain\Dto\IndexData;
use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PostProcessIndexCreationHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PostProcessIndexingHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PostProcessRuntimeIndexingHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PostRealIndexNameGenerationHookInterface;
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
    protected $activeIndex;

    /**
     * @var \Elastica\Index
     */
    protected $inactiveIndex;

    /**
     * @var array
     */
    protected $realIndexNames = [];

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
        $this->initRealIndexNames();
    }

    /**
     * Initialize the real index names. The given identifier is just an alias
     */
    protected function initRealIndexNames(): void
    {
        $this->realIndexNames = [
            $this->identifier . '_' . 'a',
            $this->identifier . '_' . 'b'
        ];
        $parameters = [$this->identifier, &$this->realIndexNames, $this];
        $this->executeHook(PostRealIndexNameGenerationHookInterface::class, $parameters);
    }

    /**
     * @param bool $reCreate
     */
    public function create(bool $reCreate = false)
    {
        $index = $this->getActiveIndex();
        if ($index->exists() === false || $reCreate === true) {
            $indexConfiguration = [
                'settings' => [
                    'number_of_shards'   => $this->configuration['config']['shards'],
                    'number_of_replicas' => $this->configuration['config']['replicas'],
                    'analysis'           => [
                        'analyzer' => $this->configuration['analyzer'],
                        'filter'   => $this->configuration['filter']
                    ]
                ]
            ];
            $parameter = [&$indexConfiguration, $this];
            $this->executeHook(PreProcessIndexConfigurationHookInterface::class, $parameter);
            $mapping = $this->getMapping()->get();
            $aliasCreated = false;
            foreach ($this->realIndexNames as $indexName) {
                $realIndex = Client::get($this->server)->getIndex($indexName);
                $realIndex->create($indexConfiguration, $reCreate);
                if ($aliasCreated === false) {
                    $realIndex->addAlias($this->identifier);
                    $aliasCreated = true;
                }
                $mapping->send($realIndex);
            }
            $this->activeIndex = null;
            $this->getActiveIndex();
            $parameter = [$this];
            $this->executeHook(PostProcessIndexCreationHookInterface::class, $parameter);
        }
    }

    /**
     * @return \Elastica\Index
     */
    protected function getActiveIndex(): \Elastica\Index
    {
        if (empty($this->activeIndex)) {
            foreach($this->realIndexNames as $indexName) {
                $index = Client::get($this->server)->getIndex($indexName);
                if($index->hasAlias($this->identifier)) {
                    $this->activeIndex = $index;
                    break;
                }
            }
        }
        if(empty($this->activeIndex)) {
            $this->activeIndex = Client::get($this->server)->getIndex($this->realIndexNames[0]);
            $this->activeIndex->addAlias($this->identifier);
        }

        return $this->activeIndex;
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
        $index = $this->getActiveIndex();
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
        $index = $this->getActiveIndex();
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
        return $this->getActiveIndex()->exists();
    }

    /**
     *
     */
    public function delete(): void
    {
        $this->getActiveIndex()->delete();
    }

    /**
     * Index a whole data set
     *
     * @param bool $secondRun
     */
    public function index(bool $secondRun = false): void
    {
        $parameter = [$this];
        $this->executeHook(PreProcessIndexingHookInterface::class, $parameter);
        foreach ($this->configuration['config']['indexer'] ?? [] as $indexerIdentifier) {
            $indexer = IndexerRegistry::getIndexer($indexerIdentifier);
            if (!empty($indexer)) {
                $indexer->setIndex($this)->index();
            }
        }
        $this->switchIndexes();
        if ($secondRun === false) {
            $this->index(true);
        }
        $this->executeHook(PostProcessIndexingHookInterface::class, $parameter);
    }

    /**
     * Switch index (a => b or b => a)
     */
    public function switchIndexes(): void
    {
        $this->getActiveIndex();
        $this->getInactiveIndex();
        if($this->activeIndex->exists()) {
            $this->activeIndex->removeAlias($this->identifier);
            $this->activeIndex = null;
        }
        if($this->inactiveIndex->exists()) {
            $this->inactiveIndex->addAlias($this->identifier);
            $this->inactiveIndex = null;
        }
        $this->getActiveIndex();
        $this->getInactiveIndex();
    }

    /**
     * @return \Elastica\Index
     */
    protected function getInactiveIndex(): \Elastica\Index
    {
        if (empty($this->inactiveIndex)) {
            foreach ($this->realIndexNames as $indexName) {
                $index = Client::get($this->server)->getIndex($indexName);
                if (!$index->hasAlias($this->identifier)) {
                    $this->inactiveIndex = $index;
                    break;
                }
            }
        }

        return $this->inactiveIndex;
    }

    /**
     * @return \Elastica\Index
     */
    public function getElasticaIndex(): \Elastica\Index
    {
        return $this->getActiveIndex();
    }
    /**
     * @param IndexData $indexData
     * @param bool      $secondRun
     */
    public function runtimeIndex(IndexData $indexData, bool $secondRun = false): void
    {
        if(!empty($this->configuration['config']['indexer'])) {
            $parameter = [$indexData, $this];
            $this->executeHook(PreProcessRuntimeIndexingHookInterface::class, $parameter);
            foreach ($this->configuration['config']['indexer'] ?? [] as $indexerIdentifier) {
                $indexer = IndexerRegistry::getRuntimeIndexer($indexerIdentifier);
                if (!empty($indexer)) {
                    $indexer->setIndex($this)->index($indexData);
                }
            }
            $this->switchIndexes();
            if ($secondRun === false) {
                $this->runtimeIndex($indexData, true);
            }
            $this->executeHook(PostProcessRuntimeIndexingHookInterface::class, $parameter);
        }
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