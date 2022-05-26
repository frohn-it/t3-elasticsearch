<?php


namespace BeFlo\T3Elasticsearch\Service;


use BeFlo\T3Elasticsearch\Aggregation\AggregationManager;
use BeFlo\T3Elasticsearch\Configuration\ConfigurationManager;
use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Exceptions\InvalidConfigurationException;
use BeFlo\T3Elasticsearch\Filter\FilterInterface;
use BeFlo\T3Elasticsearch\Filter\FilterManager;
use BeFlo\T3Elasticsearch\Hook\Interfaces\AggregationConfigurationCollectPostProcessHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\BaseSearchQueryPostProcessHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PostProcessBaseFilterArrayHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PostProcessFilterHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PostProcessFinalSearchQueryHookInterface;
use BeFlo\T3Elasticsearch\Index\Index;
use BeFlo\T3Elasticsearch\Server\Client;
use BeFlo\T3Elasticsearch\Utility\HookTrait;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class SearchService
{
    use HookTrait;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var AggregationManager
     */
    private $aggregationManager;

    /**
     * SearchService constructor.
     * @param AggregationManager $aggregationManager
     */
    public function __construct(AggregationManager $aggregationManager)
    {
        $this->aggregationManager = $aggregationManager;
    }

    /**
     * @param array $configuration
     * @param string $searchTerm
     * @return array
     * @throws InvalidConfigurationException
     */
    public function search(array $configuration, string $searchTerm): array
    {
        $this->initializeSearchConfiguration($configuration);
        $query = $this->getBaseSearchQuery($searchTerm);
        $this->addFilter($query, $this->addAggregations($query));
        $path = $this->getPath();
        $method = 'POST';
        $parameters = [
            &$query,
            &$path,
            &$method,
            $this
        ];
        $this->executeHook(PostProcessFinalSearchQueryHookInterface::class, $parameters);
        echo '<pre>================<p>' . __CLASS__ . '::' . __LINE__ . '</p>';
        var_dump(json_encode($query));
        var_dump($path);
        echo '<p>================</p></pre>';
        die();
        $result = $this->client->search($this->getPath(), $query);

        return $result;
    }

    /**
     * @param array $query
     *
     * @return array
     */
    private function addAggregations(array &$query): array
    {
        $aggregationConfigurations = $this->getAggregationConfigurations();
        if(!empty($aggregationConfigurations)) {
            $aggregations = $this->aggregationManager->getAggregations($aggregationConfigurations);
            $parameters = [&$aggregations];
            $this->executeHook(AggregationConfigurationCollectPostProcessHookInterface::class, $parameters);
            if(!empty($aggregations)) {
                $query['aggs'] = $aggregations;
            }
        }

        return $aggregationConfigurations;
    }

    /**
     * @param array $query
     * @param array $aggregations
     */
    private function addFilter(array &$query, array $aggregations): void
    {
        $filter = [];
        $arguments = GeneralUtility::_GP('tx_t3elasticsearch_elasticsearch');
        if(!empty($arguments['filter'])) {
            $configurations = [];
            foreach($arguments['filter'] as $key => $data) {
                if(!empty($aggregations[$key])) {
                    $configurations[$key] = $aggregations[$key];
                }
            }
            $filter = $this->aggregationManager->getFilter($configurations);
        }
        $baseFilterArray = $this->getFilterForIndexes();
        foreach($baseFilterArray as $baseFilter) {
            $part = $baseFilter->getQueryFilterPart();
            if(!empty($part)) {
                $filter[] = $part;
            }
        }
        $parameters = [&$filter];
        $this->executeHook(PostProcessFilterHookInterface::class, $parameters);
        if(!empty($filter)) {
            $query['query']['bool']['filter'] = $filter;
        }
    }

    /**
     * @return FilterInterface[]
     */
    private function getFilterForIndexes(): array
    {
        $result = [];
        if(!empty($this->configuration['settings']['indexes'])) {
            $indexArray = GeneralUtility::trimExplode(',', $this->configuration['settings']['indexes'], true);
            if(!empty($indexArray)) {
                $filterManager = GeneralUtility::makeInstance(FilterManager::class);
                $indexes = $this->client->getServer()->getIndexes();
                foreach($indexArray as $indexName) {
                    $index = $indexes->find($indexName);
                    if($index instanceof Index) {
                        $configuration = $index->getConfiguration();
                        if(!empty($configuration['filter_objects']) && is_array($configuration['filter_objects'])) {
                            foreach($configuration['filter_objects'] as $filterClassName => $filterConfiguration) {
                                $identifier = md5(serialize([$filterClassName => $filterConfiguration]));
                                if(empty($result[$identifier]) && ($filter = $filterManager->getFilter($filterClassName)) !== null) {
                                    $filter->setConfiguration($filterConfiguration);
                                    $result[$identifier] = $filter;
                                }
                            }
                        }
                    }
                }
            }
        }
        $params = [&$result];
        $this->executeHook(PostProcessBaseFilterArrayHookInterface::class, $params);

        return $result;
    }

    /**
     * @return string
     */
    private function getPath(): string
    {
        $result = '/_search';
        if(!empty($this->configuration['settings']['indexes'])) {
            $result = '/' . trim($this->configuration['settings']['indexes'], '/') . '/_search';
        }

        return $result;
    }

    /**
     * @param array $configuration
     * @throws InvalidConfigurationException
     */
    private function initializeSearchConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
        if(empty($configuration['settings']['server'])) {
            throw new InvalidConfigurationException('No server is configured for the search request!');
        }
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $server = $configurationManager->getServer($configuration['settings']['server']);
        if(!($server instanceof Server)) {
            throw new InvalidConfigurationException(sprintf('The given server identifier "%s" does not exist or is not reachable!', $configuration['settings']['server']));
        }
        $this->client = Client::get($server);
    }

    /**
     * @param string $searchTerm
     * @return array
     */
    protected function getBaseSearchQuery(string $searchTerm): array
    {
        $result = [
            'query' => [
                'bool' => [
                    'must' => [
                        'query_string' => [
                            'query' => '*' . $searchTerm . '*',
                            'default_operator' => ($this->configuration['settings']['defaultOperator'] ?? 'and'),
                            'analyze_wildcard' => true,
                            'allow_leading_wildcard' => true
                        ]
                    ]
                ]
            ]
        ];
        $params = [
            &$result,
            $this
        ];
        $this->executeHook(BaseSearchQueryPostProcessHookInterface::class, $params);

        return $result;
    }

    /**
     * @return array
     */
    private function getAvailableIndexes(): array
    {
        $server = $this->client->getServer();
        $availableIndexes = [];
        foreach ($server->getIndexes() as $index) {
            $availableIndexes[$index->getIdentifier()] = $index;
        }
        return $availableIndexes;
    }

    /**
     * @return array
     */
    private function getAggregationConfigurations(): array
    {
        $aggregationConfigurations = [];
        if (!empty($this->configuration['settings']['aggregations'])) {
            $selectedAggregations = GeneralUtility::trimExplode(',', $this->configuration['settings']['aggregations']);
            $availableIndexes = $this->getAvailableIndexes();
            foreach ($selectedAggregations ?? [] as $aggregationKey) {
                $decryptedInformation = unserialize(base64_decode($aggregationKey));
                foreach ($decryptedInformation as $indexName => $aggregationIdentifier) {
                    if (!empty($availableIndexes[$indexName])) {
                        $indexConfiguration = $availableIndexes[$indexName]->getConfiguration();
                        if(!empty($indexConfiguration['aggregations'][$aggregationIdentifier])) {
                            $aggregationConfigurations[$aggregationKey] = $indexConfiguration['aggregations'][$aggregationIdentifier];
                        }
                    }
                }
            }
        }
        return $aggregationConfigurations;
    }
}