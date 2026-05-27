<?php

declare(strict_types=1);

return [
    'external_link_list_groupe' => [
        'path' => '/link/list/groupe',
        'target' => 'Cru\\ExternalLinkList\\Backend\\Controller\\AdminModuleController::groupeAction',
        'action' => 'groupe',
    ],
    'external_link_list_list' => [
        'path' => '/link/list/list',
        'target' => 'Cru\\ExternalLinkList\\Backend\\Controller\\AdminModuleController::listAction',
        'action' => 'list',
    ],
    'external_link_list_index' => [
        'path' => '/link/list/index',
        'target' => 'Cru\\ExternalLinkList\\Backend\\Controller\\AdminModuleController::indexAction',
        'action' => 'index',
    ],
];
