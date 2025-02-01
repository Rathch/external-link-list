<?php

// SPDX-FileCopyrightText: 2025 Christian Rath-Ulrich
//
// SPDX-License-Identifier: GPL-3.0-or-later

/*
  * This file is part of the package cru/external_link_list.
  *
  * Copyright (C) 2024 - 2025 Christian Rath-Ulrich
  *
  * It is free software; you can redistribute it and/or modify it under
  * the terms of the GNU General Public License, either version 3
  * of the License, or any later version.
  *
  * For the full copyright and license information, please read the
  * LICENSE file that was distributed with this source code.
  */

use Cru\ExternalLinkList\Backend\Controller\AdminModuleController;

return [
    'external_link_list' => [
        'parent' => 'web',
        'access' => 'user',
        'path' => '/module/web/external_link_list',
        'labels' => 'LLL:EXT:external_link_list/Resources/Private/Language/Module/locallang_mod.xlf',
        'extensionName' => 'ExternalLinkList',
        'iconIdentifier' => 'tx_external_link_list-backend-module',
        'routes' => [
            '_default' => [
                'target' => AdminModuleController::class . '::handleRequest',
            ],
        ],
    ],
];
