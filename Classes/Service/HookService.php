<?php


namespace BeFlo\T3Elasticsearch\Service;


use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use TYPO3\CMS\Core\SingletonInterface;

class HookService implements SingletonInterface
{

    /**
     * @var RewindableGenerator
     */
    private $hookObjects;

    /**
     * HookService constructor.
     *
     * @param RewindableGenerator $hookObjects
     */
    public function __construct(RewindableGenerator $hookObjects)
    {
        $this->hookObjects = $hookObjects;
    }

    /**
     * @param string $hookInterface
     *
     * @return array
     */
    public function getHooks(string $hookInterface): array
    {
        $result = [];
        foreach ($this->hookObjects as $hookObject) {
            if ($hookObject instanceof $hookInterface) {
                $result[] = $hookObject;
            }
        }

        return $result;
    }
}