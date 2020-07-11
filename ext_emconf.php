<?php

/**
 * Extension Manager/Repository config file for ext "t3_elasticsearch".
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 'T3 Elasticsearch',
    'description' => '',
    'category' => 'templates',
    'constraints' => [
        'depends' => [
            'typo3' => '10.2.0-10.4.99',
            'fluid_styled_content' => '10.2.0-10.4.99',
            'rte_ckeditor' => '10.2.0-10.4.99',
        ],
        'conflicts' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'BeFlo\\T3Elasticsearch\\' => 'Classes',
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Be Flo',
    'author_email' => '',
    'author_company' => 'Be Flo',
    'version' => '1.0.0',
];
