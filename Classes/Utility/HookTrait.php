<?php


namespace BeFlo\T3Elasticsearch\Utility;


use BeFlo\T3Elasticsearch\Service\HookService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait HookTrait
{
    /**
     * @var HookService
     */
    private static $hookService;

    /**
     * @param string $hookInterfaceName
     * @param array  ...$parameter
     */
    protected function executeHook(string $hookInterfaceName, array &$parameter)
    {
        if(!self::$hookService instanceof HookService) {
            self::$hookService = GeneralUtility::makeInstance(HookService::class);
        }
        $methods = get_class_methods($hookInterfaceName);
        foreach (self::$hookService->getHooks($hookInterfaceName) as $hookObject) {
            foreach ($methods as $method) {
                $hookObject->{$method}(...$parameter);
            }
        }
    }
}