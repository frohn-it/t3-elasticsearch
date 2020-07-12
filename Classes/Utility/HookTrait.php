<?php


namespace BeFlo\T3Elasticsearch\Utility;


use TYPO3\CMS\Core\Utility\GeneralUtility;

trait HookTrait
{

    /**
     * @var array
     */
    protected $hookObjects = [];


    /**
     * @param string $baseClassName
     *
     * @return void
     */
    protected function initHooks(string $baseClassName): void
    {
        foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$baseClassName] ?? [] as $className) {
            $this->hookObjects[] = GeneralUtility::makeInstance($className);
        }
    }


    /**
     * @param string $hookInterfaceName
     * @param array  ...$parameter
     */
    protected function executeHook(string $hookInterfaceName, array &$parameter)
    {
        $methods = get_class_methods($hookInterfaceName);
        foreach($this->hookObjects as $hookObject) {
            if($hookObject instanceof $hookInterfaceName) {
                foreach($methods as $method) {
                    $hookObject->{$method}(...$parameter);
                }
            }
        }
    }
}