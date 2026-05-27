<?php

declare(strict_types=1);

namespace Cru\ExternalLinkList\Tests\Functional\Service;

use Cru\ExternalLinkList\Service\ProvideExternalLinkListService;
use Cru\ExternalLinkList\Service\ProvideParsedLinkListService;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class ExtensionServicesTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/external_link_list',
    ];

    #[Test]
    public function provideExternalLinkListServiceCanBeResolved(): void
    {
        self::assertInstanceOf(ProvideExternalLinkListService::class, $this->get(ProvideExternalLinkListService::class));
    }

    #[Test]
    public function provideParsedLinkListServiceCanBeResolved(): void
    {
        self::assertInstanceOf(ProvideParsedLinkListService::class, $this->get(ProvideParsedLinkListService::class));
    }
}
