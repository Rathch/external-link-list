<?php

declare(strict_types=1);

namespace Cru\ExternalLinkList\Tests\Functional\Service;

use Cru\ExternalLinkList\Service\ProvideExternalLinkListService;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class ProvideExternalLinkListServiceTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/external_link_list',
    ];

    private ProvideExternalLinkListService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');

        $this->subject = $this->get(ProvideExternalLinkListService::class);
    }

    #[Test]
    public function getConfigurationListsExternalPagesWithUrl(): void
    {
        $configuration = $this->subject->getConfiguration();

        self::assertCount(1, $configuration);
        self::assertSame(2, $configuration[0]['uid']);
        self::assertSame('External page', $configuration[0]['title']);
        self::assertSame('https://example.com', $configuration[0]['url']);
    }

    #[Test]
    public function getConfigurationKeepsExistingUrlField(): void
    {
        $configuration = $this->subject->getConfiguration();

        self::assertArrayHasKey('url', $configuration[0]);
        self::assertNotEmpty($configuration[0]['url']);
    }

    #[Test]
    public function getConfigurationExcludesLinkPageWithInternalTypo3Url(): void
    {
        $configuration = $this->subject->getConfiguration();

        $uids = array_column($configuration, 'uid');
        self::assertNotContains(3, $uids);
    }

    #[Test]
    public function getConfigurationDoesNotIncludeStandardPages(): void
    {
        $configuration = $this->subject->getConfiguration();

        $uids = array_column($configuration, 'uid');
        self::assertNotContains(1, $uids);
        self::assertNotContains(4, $uids);
    }

    #[Test]
    public function getConfigurationReturnsOnlyExternalLinkPages(): void
    {
        $configuration = $this->subject->getConfiguration();

        foreach ($configuration as $page) {
            self::assertSame(3, (int)$page['doktype']);
            self::assertNotEmpty($page['url']);
            self::assertStringStartsNotWith('t3://', (string)$page['url']);
        }
    }
}
