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

declare(strict_types=1);

namespace Cru\ExternalLinkList\Service;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class ProvideParsedLinkListService
{
    public function __construct() {}

    public function getConfiguration(bool $useCache = true): array
    {
        $cacheFile = Environment::getVarPath() . '/cache/data/links.json';
        $externalLinks = [];

        if ($useCache === true
            && file_exists($cacheFile)
            && filemtime($cacheFile) > time() - 600
        ) {
            return json_decode(file_get_contents($cacheFile), true);
        } else {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
            $records = $queryBuilder
                ->select('uid', 'bodytext', 'pid')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->like('bodytext', $queryBuilder->createNamedParameter('%<a%', \PDO::PARAM_STR))
                )
                ->executeQuery()
                ->fetchAllAssociative();
            foreach ($records as $record) {
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
                $page = $queryBuilder
                ->select('uid','title')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq('uid', $record['pid'])
                )
                ->executeQuery()
                ->fetchAssociative();

                $dom = new \DOMDocument();
                @$dom->loadHTML($record['bodytext'], LIBXML_NOERROR);
                $anchors = $dom->getElementsByTagName('a');
                $i =0;
                foreach ($anchors as $anchor) {
                    $href = trim($anchor->getAttribute('href'));
                    if (!str_starts_with($href, 't3://') && filter_var($href, FILTER_VALIDATE_URL)) {
                        $externalLinks[$record['uid']][$i]['href'] = $href;
                        $externalLinks[$record['uid']][$i]['uid'] = $record['uid'];
                        $externalLinks[$record['uid']][$i]['pid'] = $record['pid'];
                        $externalLinks[$record['uid']][$i]['title'] = $page['title'];
                        $i++;
                    }
                }
            }
            
            GeneralUtility::writeFileToTypo3tempDir($cacheFile, json_encode($externalLinks));
        }
        return $externalLinks;
    }
}
