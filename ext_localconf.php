<?php
defined('TYPO3_MODE') || die();

call_user_func(function() {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][] = \BeFlo\T3Elasticsearch\Backend\ToolbarItems\ElasticSearchToolbarItem::class;

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    $iconRegistry->registerIcon(
        't3_elasticsearch_toolbar_es_icon',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:t3_elasticsearch/Resources/Public/Icons/logo_es_toolbar.svg']
    );

    $cacheKey = \BeFlo\T3Elasticsearch\Constants::EXT_KEY . '_cache';
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheKey])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheKey] = [];
    }
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheKey]['backend'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheKey]['backend'] = \TYPO3\CMS\Core\Cache\Backend\FileBackend::class;
    }
});
