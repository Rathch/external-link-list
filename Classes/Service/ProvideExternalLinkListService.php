<?php

// SPDX-FileCopyrightText: 2025 Christian Rath-Ulrich
//
// SPDX-License-Identifier: GPL-3.0-or-later

/*
  * This file is part of the package cru/psr14-event-list.
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

declare(strict_types=1);

namespace Cru\ExternalLinkList\Service;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class ProvideExternalLinkListService
{
    public function __construct() {}

    public function getConfiguration(bool $useCache = true, bool $fetchDocs = true, ?OutputInterface $cliOutput = null): array
    {

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');

        $externalLinks = $queryBuilder
            ->select('uid', 'pid', 'header', 'header_link')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->like('header_link', $queryBuilder->createNamedParameter('http%', \PDO::PARAM_STR))
            )
            ->executeQuery()
            ->fetchAllAssociative();

        return $externalLinks;
    }
}
