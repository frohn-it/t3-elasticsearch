<?php

call_user_func(function(string $extKey, string $table) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        $extKey,
        'ElasticSearch',
        'LLL:EXT:t3_elasticsearch/Resources/Private/Language/locallang_be.xlf:plugin.title',
        'EXT:t3_elasticsearch/Resources/Public/Icons/Extension.svg'
    );
}, 't3_elasticsearch', basename(__FILE__, '.php'));