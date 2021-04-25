<?php
defined('TYPO3_MODE') || die();

call_user_func(function(string $extKey, string $table) {
    $LLL = 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:' . $table . '.';

    $newColumns = [
        'tx_t3elasticsearch_last_indexed' => [
            'exclude' => true,
            'label' => $LLL . 'fields.tx_t3elasticsearch_last_indexed',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'date,int',
                'range' => [
                    'upper' => 2145913200,
                ],
                'default' => 0
            ]
        ],
        
    ];

    $GLOBALS['TCA'][$table]['columns'] = array_replace_recursive($GLOBALS['TCA'][$table]['columns'], $newColumns);

}, \BeFlo\T3Elasticsearch\Constants::EXT_KEY, basename(__FILE__, '.php'));