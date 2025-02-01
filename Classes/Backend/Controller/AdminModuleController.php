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

use Cru\ExternalLinkList\Service\ProvideExternalLinkListService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;

#[AsController]
final class AdminModuleController
{
    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly ProvideExternalLinkListService $provideExternalLinkListService,
    ) {}

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        return $this->indexAction($request);
    }

    public function indexAction(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $moduleTemplate->setTitle('External Link List');
        $moduleTemplate->assign('configuration', $this->provideExternalLinkListService->getConfiguration());
        return $moduleTemplate->renderResponse('AdminModule/Index');
    }

}
