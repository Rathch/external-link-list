<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'external-link-list',
    'description' => 'Adds a Module to list all external links',
    'category' => 'be',
    'state' => 'stable',
    'author' => 'Christian Rath-Ulrich',
    'author_email' => 'christian@rath-ulrich.de',
    'author_company' => '',
    'version' => '2.1.0',
    'constraints' => [
        'depends' => [
            'php' => '8.1.0-8.4.99',
            'typo3' => '12.4.2-14.99.99',
            'backend' => '12.4.2-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
