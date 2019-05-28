<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Images Compressor',
    'description' => 'Additional optimize (compress) FE images after processing.',
    'category' => 'fe',
    'author' => 'Andriy Oprysko',
    'author_email' => '',
    'author_company' => 'andriy.oprysko@resultify.com',
    'state' => 'stable',
    'createDirs' => '',
    'clearCacheOnLoad' => false,
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.5.99'
        ],
        'conflicts' => [],
        'suggests' => []
    ]
];
