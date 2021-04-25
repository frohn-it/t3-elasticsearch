<?php


namespace BeFlo\T3Elasticsearch\TCA;


use BeFlo\T3Elasticsearch\Configuration\ConfigurationManager;
use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PluginUserFunc
{

    /**
     * @param array $params
     */
    public function getAvailableServer(array &$params): void
    {
        $items = &$params['items'];
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $configuration = $configurationManager->getConfiguration();
        $items[] = ['', ''];
        /** @var Server $server */
        foreach ($configuration['server'] ?? [] as $server) {
            $identifier = $server->getIdentifier();
            $items[] = [$identifier, $identifier];
        }
    }

    /**
     * @param array $params
     */
    public function getAvailableIndexes(array &$params): void
    {
        $server = $this->getServerFromConfiguration($params);
        if ($server instanceof Server) {
            foreach ($server->getIndexes() as $index) {
                $params['items'][] = [$index->getIdentifier(), $index->getIdentifier()];
            }
        }
    }

    /**
     * @param array $params
     * @return Server|null
     */
    private function getServerFromConfiguration(array $params): ?Server
    {
        $server = null;
        if (!empty($params['row']['settings.server'])) {
            $serverName = $params['row']['settings.server'];
            if (is_array($serverName)) {
                $serverName = current($serverName);
            }
            $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
            $server = $configurationManager->getServer($serverName);
        }

        return $server;
    }

    /**
     * @param array $params
     */
    public function getAvailableAggregations(array &$params): void
    {
        if (!empty($params['row']['settings.indexes'])) {
            $server = $this->getServerFromConfiguration($params);
            if ($server instanceof Server) {
                $indexArray = $params['row']['settings.indexes'];
                if (!is_array($indexArray)) {
                    $indexArray = GeneralUtility::trimExplode(',', $indexArray, true);
                }
                foreach ($server->getIndexes() as $index) {
                    $identifier = $index->getIdentifier();
                    if (in_array($identifier, $indexArray)) {
                        $indexConfiguration = $index->getConfiguration();
                        if (!empty($indexConfiguration['aggregations']) && is_array($indexConfiguration['aggregations'])) {
                            foreach ($indexConfiguration['aggregations'] as $aggIdentifier => $configuration) {
                                $value = base64_encode(serialize([$identifier => $aggIdentifier]));
                                $params['items'][] = [$this->getLabel($indexConfiguration['label']) . ': ' . $this->getLabel($configuration['label']), $value];
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $keyOrLabel
     * @return string
     */
    private function getLabel(string $keyOrLabel): string
    {
        /** @var LanguageService $languageService */
        static $languageService;
        if (empty($languageService)) {
            if (!empty($GLOBALS['LANG'])) {
                $languageService = $GLOBALS['LANG'];
            } else {
                $languageService = GeneralUtility::makeInstance(LanguageService::class);
            }
        }
        $label = $languageService->sL($keyOrLabel);
        if (empty($label)) {
            $label = $keyOrLabel;
        }

        return $label;
    }
}