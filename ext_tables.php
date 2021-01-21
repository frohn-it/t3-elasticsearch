<?php
defined('TYPO3_MODE') || die();

call_user_func(function(string $extKey) {

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        $extKey,
        'tx_t3elasticsearch',
        '',
        '',
        [],
        [
            'access' => 'user,usergroup',
            'icon' => 'EXT:beuser/Resources/Public/Icons/module-beuser.svg',
            'labels' => 'LLL:EXT:t3_elasticsearch/Resources/Private/Language/locallang_mod.xlf',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        $extKey,
        'tx_t3elasticsearch',
        'tx_t3elasticsearch_config_generator',
        'top',
        [
            \BeFlo\T3Elasticsearch\Controller\RecordConfigurationGeneratorController::class => 'index,configure,generate'
        ],
        [
            'access' => 'user,usergroup',
            'icon' => 'EXT:beuser/Resources/Public/Icons/module-beuser.svg',
            'labels' => 'LLL:EXT:t3_elasticsearch/Resources/Private/Language/locallang_mod.generator.xlf',
        ]
    );

}, 't3_elasticsearch');