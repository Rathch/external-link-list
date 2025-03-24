<?php

// SPDX-FileCopyrightText: 2025 Christian Rath-Ulrich
//
// SPDX-License-Identifier: GPL-3.0-or-later

/*
  * This file is part of the package cru/external-link-list.
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

return [
    'tx_link_list_groupe' => [
        'path' => '/link/list/groupe',
        'target' => 'Cru\\ExternalLinkList\\Backend\\Controller\\AdminModuleController::groupeAction',
        'action' => 'groupe',
    ],
    'tx_link_list_list' => [
        'path' => '/link/list/list',
        'target' => 'Cru\\ExternalLinkList\\Backend\\Controller\\AdminModuleController::listAction',
        'action' => 'list',
    ],
    'tx_link_list_index' => [
        'path' => '/link/list/index',
        'target' => 'Cru\\ExternalLinkList\\Backend\\Controller\\AdminModuleController::indexAction',
        'action' => 'index',
    ],
];
