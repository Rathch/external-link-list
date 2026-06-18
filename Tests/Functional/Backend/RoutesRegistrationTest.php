<?php

declare(strict_types=1);

namespace Cru\ExternalLinkList\Tests\Functional\Backend;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\Module\ModuleProvider;
use TYPO3\CMS\Backend\Routing\Router;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class RoutesRegistrationTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'backend',
    ];

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/external_link_list',
    ];

    #[Test]
    public function linkListRoutesAreRegistered(): void
    {
        $router = GeneralUtility::makeInstance(Router::class);

        self::assertTrue($router->hasRoute('external_link_list_index'));
        self::assertTrue($router->hasRoute('external_link_list_list'));
        self::assertTrue($router->hasRoute('external_link_list_groupe'));
    }

    #[Test]
    public function backendModuleIsRegistered(): void
    {
        $moduleProvider = $this->get(ModuleProvider::class);
        $webModule = $moduleProvider->getModule('web');
        self::assertNotNull($webModule);

        $registered = false;
        foreach ($webModule->getSubModules() as $subModule) {
            if ($subModule->getPath() === '/module/web/external_link_list') {
                $registered = true;
                break;
            }
        }

        self::assertTrue($registered, 'Expected external_link_list submodule under web');
    }
}
