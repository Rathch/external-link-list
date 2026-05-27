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
use TYPO3\CMS\Core\LinkHandling\LinkService;

final class ProvideExternalLinkListService
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly LinkService $linkService,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function getConfiguration(bool $useCache = true, bool $fetchDocs = true, ?OutputInterface $cliOutput = null): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');

        $linkPages = $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('doktype', '3'),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $externalLinks = [];
        foreach ($linkPages as $page) {
            $page = $this->normalizePageLinkRow($page);
            if ($this->isExternalLinkPage($page)) {
                $externalLinks[] = $page;
            }
        }

        return $externalLinks;
    }

    /**
     * Ensures a "url" key for templates: TYPO3 v12/v13 use pages.url, v14+ uses pages.link (typolink).
     *
     * @param array<string, mixed> $page
     * @return array<string, mixed>
     */
    private function normalizePageLinkRow(array $page): array
    {
        $url = (string)($page['url'] ?? '');
        if ($url !== '' && !$this->isTypo3ResourceUri($url)) {
            return $page;
        }

        $link = (string)($page['link'] ?? '');
        if ($link !== '') {
            $page['url'] = $this->resolveLinkFieldForDisplay($link);
        }

        return $page;
    }

    /**
     * TYPO3 v12/v13: pages.url holds plain https:// targets (no t3://).
     * TYPO3 v14+: only typolink type "url" counts as external (t3://page etc. are excluded).
     *
     * @param array<string, mixed> $page
     */
    private function isExternalLinkPage(array $page): bool
    {
        $url = (string)($page['url'] ?? '');
        if ($url !== '' && !$this->isTypo3ResourceUri($url)) {
            return true;
        }

        $link = (string)($page['link'] ?? '');
        if ($link === '') {
            return false;
        }

        try {
            $linkDetails = $this->linkService->resolve($link);

            return ($linkDetails['type'] ?? '') === LinkService::TYPE_URL;
        } catch (\Throwable) {
            return !$this->isTypo3ResourceUri($link) && filter_var($link, FILTER_VALIDATE_URL) !== false;
        }
    }

    private function isTypo3ResourceUri(string $value): bool
    {
        return str_starts_with(strtolower($value), 't3://');
    }

    private function resolveLinkFieldForDisplay(string $link): string
    {
        try {
            $linkDetails = $this->linkService->resolve($link);

            if (($linkDetails['type'] ?? '') === LinkService::TYPE_URL) {
                return (string)($linkDetails['url'] ?? $this->linkService->asString($linkDetails));
            }

            return $this->linkService->asString($linkDetails);
        } catch (\Throwable) {
            return $link;
        }
    }
}
