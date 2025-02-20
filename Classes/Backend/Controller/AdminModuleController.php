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

namespace Cru\ExternalLinkList\Backend\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use Cru\ExternalLinkList\Service\ProvideParsedLinkListService;
use Cru\ExternalLinkList\Service\ProvideExternalLinkListService;

#[AsController]
final class AdminModuleController
{
    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly ProvideExternalLinkListService $provideExternalLinkListService,
        private readonly ProvideParsedLinkListService $provideParsedLinkListService,
    ) {}

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $this->setUpMenu($request, $moduleTemplate);

        return $this->indexAction($request);
    }

    public function indexAction(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $this->setUpMenu($request, $moduleTemplate);

        $moduleTemplate->setTitle('External Link List');
        $moduleTemplate->assign('links', $this->provideExternalLinkListService->getConfiguration());
        return $moduleTemplate->renderResponse('AdminModule/Index');
    }

    public function listAction(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $this->setUpMenu($request, $moduleTemplate);

        $moduleTemplate->setTitle('External Link List');
        
        $moduleTemplate->assign('links', $this->provideParsedLinkListService->getConfiguration());
        return $moduleTemplate->renderResponse('AdminModule/List');
    }

    private function setUpMenu(ServerRequestInterface $request, ModuleTemplate $moduleTemplate): void
    {
        $menu = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('cruExternalLinkList');

        $menuItems = [
            'index' => [
                'controller' => 'Module',
                'action' => 'index',
                'route' => 'tx_link_list_index',
                'label' => 'List External Link (Pages)',
            ],
            'list' => [
                'controller' => 'Module',
                'action' => 'listCoreEventsAction',
                'route' => 'tx_link_list_list',
                'label' => 'List parsed links (RTE)',
            ],
        ];

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        foreach ($menuItems as $menuItemConfig) {
            $currentUri = $request->getUri();
            $action = $menuItemConfig['route'];
            $uri = $uriBuilder->buildUriFromRoute($action, [$request]);
            $isActive = ($currentUri === $uri);
            $menuItem = $menu->makeMenuItem()
                            ->setTitle($menuItemConfig['label'])
                            ->setHref($uri)
                            ->setActive($isActive);
            $menu->addMenuItem($menuItem);
        }

        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

}
