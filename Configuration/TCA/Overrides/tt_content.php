<?php

call_user_func(function(string $extKey, string $table) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        $extKey,
        'ElasticSearch',
        'LLL:EXT:t3_elasticsearch/Resources/Private/Language/locallang_be.xlf:plugin.title',
        'EXT:t3_elasticsearch/Resources/Public/Icons/Extension.svg'
    );
    $pluginSignature = 't3elasticsearch_elasticsearch';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        $pluginSignature,
        'FILE:EXT:t3_elasticsearch/Configuration/FlexForm/SearchControllerFlexForm.xml'
    );

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'pages,recursive';

}, 't3_elasticsearch', basename(__FILE__, '.php'));