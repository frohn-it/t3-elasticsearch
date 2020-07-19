<?php


namespace BeFlo\T3Elasticsearch\Registry;


use BeFlo\T3Elasticsearch\Constants;
use BeFlo\T3Elasticsearch\Exceptions\UnexpectedClassException;
use BeFlo\T3Elasticsearch\Indexer\IndexerInterface;
use BeFlo\T3Elasticsearch\Indexer\RuntimeIndexerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IndexerRegistry
{
    protected const SCOPE_NORMAL = 'indexer';
    protected const SCOPE_RUNTIME = 'indexer_runtime';

    /**
     * @param string $className
     *
     * @throws UnexpectedClassException
     */
    public static function registerIndexer(string $className): void
    {
        self::addIndexer($className, IndexerInterface::class, self::SCOPE_NORMAL);
    }

    /**
     * @param string $className
     * @param string $expectedInterface
     * @param string $scope
     *
     * @throws UnexpectedClassException
     */
    protected static function addIndexer(string $className, string $expectedInterface, string $scope): void
    {
        $interfaces = class_implements($className);
        if (!in_array($expectedInterface, $interfaces)) {
            throw new UnexpectedClassException(sprintf('The indexer "%s" must implement the interface "%s"', $className, $expectedInterface));
        }
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][Constants::EXT_KEY]['registry'][$scope][$className::getIdentifier()] = $className;
    }

    /**
     * @param string $className
     *
     * @throws UnexpectedClassException
     */
    public static function registerRuntimeIndexer(string $className): void
    {
        self::addIndexer($className, RuntimeIndexerInterface::class, self::SCOPE_RUNTIME);
    }

    /**
     * @param string $identifier
     *
     * @return IndexerInterface|null
     */
    public static function getIndexer(string $identifier): ?IndexerInterface
    {
        return self::getIndexerObjectOrNull($identifier, self::SCOPE_NORMAL);
    }

    /**
     * @param string $identifier
     * @param string $scope
     *
     * @return IndexerInterface|null
     */
    protected static function getIndexerObjectOrNull(string $identifier, string $scope): ?IndexerInterface
    {
        $result = null;
        $className = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][Constants::EXT_KEY]['registry'][$scope][$identifier] ?? null;
        if (!empty($className)) {
            $result = GeneralUtility::makeInstance($className);
        }

        return $result;
    }

    /**
     * @param string $identifier
     *
     * @return RuntimeIndexerInterface|null
     */
    public static function getRuntimeIndexer(string $identifier): ?RuntimeIndexerInterface
    {
        return self::getIndexerObjectOrNull($identifier, self::SCOPE_RUNTIME);
    }
}