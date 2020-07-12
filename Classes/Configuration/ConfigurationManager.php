<?php


namespace BeFlo\T3Elasticsearch\Configuration;


use BeFlo\T3Elasticsearch\Hook\Interfaces\ConfigurationManagerCachePostProcessHookInterface;
use BeFlo\T3Elasticsearch\Server\ServerLoader;
use BeFlo\T3Elasticsearch\Utility\HookTrait;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;

class ConfigurationManager implements SingletonInterface
{
    use HookTrait;

    protected const BASE_CACHE_IDENTIFIER = 'base_configuration';

    /**
     * @var ServerLoader
     */
    protected $serverLoader;

    /**
     * @var FrontendInterface
     */
    protected $cache;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * ConfigurationManager constructor.
     *
     * @param ServerLoader      $serverLoader
     * @param FrontendInterface $cache
     */
    public function __construct(ServerLoader $serverLoader, FrontendInterface $cache)
    {
        $this->serverLoader = $serverLoader;
        $this->cache = $cache;
        $this->initHooks(ConfigurationManager::class);
        $this->loadConfigurationFromCache();
    }

    /**
     * Initialize the configuration. Either load it from the cache or create the configuration and cache it
     */
    protected function loadConfigurationFromCache(): void
    {
        if (($configuration = $this->cache->get(self::BASE_CACHE_IDENTIFIER)) === false) {
            $configuration = $this->createConfiguration();
            $this->cache->set(self::BASE_CACHE_IDENTIFIER, $configuration, [], 0);
        }
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    protected function createConfiguration()
    {
        $configuration = [
            'server' => $this->serverLoader->loadAvailableServer()
        ];
        $parameter = [$configuration, $this];
        $this->executeHook(ConfigurationManagerCachePostProcessHookInterface::class, $parameter);

        return $configuration;
    }

    /**
     * Purge the cached configuration
     */
    public function purgeConfiguration()
    {
        $this->cache->remove(self::BASE_CACHE_IDENTIFIER);
        $this->loadConfigurationFromCache();
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}