<?php


namespace BeFlo\T3Elasticsearch\Hook;


use BeFlo\T3Elasticsearch\Configuration\ConfigurationManager;
use BeFlo\T3Elasticsearch\Domain\Dto\IndexData;
use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PreRuntimeProcessDataAggregationHookInterface;
use BeFlo\T3Elasticsearch\Utility\HookTrait;
use BeFlo\T3Elasticsearch\Utility\ObjectStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class RuntimeIndexHook
{
    use HookTrait;

    /**
     * @param array $params
     */
    public function executeRuntimeIndexer(array $params)
    {
        /** @var TypoScriptFrontendController $typoScriptFrontEndController */
        $typoScriptFrontEndController = $params['pObj'];
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $configuration = $configurationManager->getConfiguration();
        if ($typoScriptFrontEndController->page['no_search'] === 0 && (!empty($configuration['server']) && $configuration['server'] instanceof ObjectStorage)) {
            $indexData = new IndexData($typoScriptFrontEndController);
            $parameter = [$indexData];
            $this->executeHook(PreRuntimeProcessDataAggregationHookInterface::class, $parameter);
            /** @var Server $server */
            foreach ($configuration['server'] as $server) {
                foreach ($server->getIndexes() as $index) {
                    $index->runtimeIndex($indexData);
                }
            }
        }
    }
}