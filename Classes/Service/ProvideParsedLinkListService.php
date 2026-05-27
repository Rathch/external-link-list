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

use DOMDocument;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ProvideParsedLinkListService
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    /**
     * @return array<int, array<int, array{href: string, uid: int, pid: int, title: string}>>
     */
    public function getConfiguration(bool $useCache = true): array
    {
        $cacheFile = Environment::getVarPath() . '/cache/data/links.json';

        if ($useCache === true) {
            $cached = $this->readCacheIfFresh($cacheFile);
            if ($cached !== null) {
                /** @var array<int, array<int, array{href: string, uid: int, pid: int, title: string}>> $cached */
                return $cached;
            }
        }

        $externalLinks = [];
        $records = $this->fetchRecordsWithLinks();

        foreach ($records as $record) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
            $page = $queryBuilder
                ->select('uid', 'title')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq('uid', $record['pid'])
                )
                ->executeQuery()
                ->fetchAssociative();

            $pageTitle = is_array($page) ? (string)($page['title'] ?? '') : '';

            $dom = $this->loadHtmlDocument($record['bodytext']);
            $anchors = $dom->getElementsByTagName('a');
            $index = 0;
            foreach ($anchors as $anchor) {
                $href = trim($anchor->getAttribute('href'));
                if ($this->isExternalHref($href)) {
                    $externalLinks[$record['uid']][$index] = [
                        'href' => $href,
                        'uid' => $record['uid'],
                        'pid' => $record['pid'],
                        'title' => $pageTitle,
                    ];
                    $index++;
                }
            }
        }

        $this->writeCache($cacheFile, $externalLinks);

        return $externalLinks;
    }

    /**
     * @return array<string, list<array{href: string, uid: int, pid: int, title: string, target: string}>>
     */
    public function getGroupeConfiguration(bool $useCache = true): array
    {
        $cacheFile = Environment::getVarPath() . '/cache/data/links_grouped.json';

        if ($useCache === true) {
            $cached = $this->readCacheIfFresh($cacheFile);
            if ($cached !== null) {
                /** @var array<string, list<array{href: string, uid: int, pid: int, title: string, target: string}>> $cached */
                return $cached;
            }
        }

        $externalLinks = [];
        $records = $this->fetchRecordsWithLinks();

        foreach ($records as $record) {
            $dom = $this->loadHtmlDocument($record['bodytext']);
            $anchors = $dom->getElementsByTagName('a');
            foreach ($anchors as $anchor) {
                $href = trim($anchor->getAttribute('href'));
                if ($this->isExternalHref($href)) {
                    $externalLinks[$href][] = [
                        'href' => $href,
                        'uid' => $record['uid'],
                        'pid' => $record['pid'],
                        'title' => trim($anchor->getAttribute('title')),
                        'target' => trim($anchor->getAttribute('target')),
                    ];
                }
            }
        }

        $this->writeCache($cacheFile, $externalLinks);

        return $externalLinks;
    }

    /**
     * @return list<array{uid: int, bodytext: string, pid: int}>
     */
    private function fetchRecordsWithLinks(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $records = $queryBuilder
            ->select('uid', 'bodytext', 'pid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->like('bodytext', $queryBuilder->createNamedParameter('%<a%', Connection::PARAM_STR))
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $normalized = [];
        foreach ($records as $record) {
            $normalized[] = [
                'uid' => (int)$record['uid'],
                'bodytext' => (string)$record['bodytext'],
                'pid' => (int)$record['pid'],
            ];
        }

        return $normalized;
    }

    private function loadHtmlDocument(string $html): DOMDocument
    {
        $dom = new DOMDocument();
        $useInternalErrors = libxml_use_internal_errors(true);
        try {
            $dom->loadHTML($html, LIBXML_NOERROR);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($useInternalErrors);
        }

        return $dom;
    }

    private function isExternalHref(string $href): bool
    {
        return $href !== ''
            && !str_starts_with($href, 't3://')
            && filter_var($href, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @return array<mixed>|null
     */
    private function readCacheIfFresh(string $cacheFile): ?array
    {
        if (!file_exists($cacheFile) || filemtime($cacheFile) <= time() - 600) {
            return null;
        }

        $contents = file_get_contents($cacheFile);
        if ($contents === false) {
            return null;
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * @param array<mixed> $data
     */
    private function writeCache(string $cacheFile, array $data): void
    {
        GeneralUtility::writeFileToTypo3tempDir(
            $cacheFile,
            json_encode($data, JSON_THROW_ON_ERROR),
        );
    }
}
