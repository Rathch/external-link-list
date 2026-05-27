<?php

declare(strict_types=1);

namespace Cru\ExternalLinkList\Tests\Functional\Service;

use Cru\ExternalLinkList\Service\ProvideParsedLinkListService;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class ProvideParsedLinkListServiceTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/external_link_list',
    ];

    private ProvideParsedLinkListService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tt_content.csv');
        $this->clearLinkCaches();

        $this->subject = new ProvideParsedLinkListService();
    }

    #[Test]
    public function getConfigurationExtractsExternalLinksFromRteContent(): void
    {
        $configuration = $this->subject->getConfiguration(useCache: false);

        self::assertArrayHasKey(1, $configuration);
        self::assertCount(1, $configuration[1]);
        self::assertSame('https://external.example/test', $configuration[1][0]['href']);
        self::assertSame(1, $configuration[1][0]['uid']);
        self::assertSame(1, $configuration[1][0]['pid']);
        self::assertSame('Root', $configuration[1][0]['title']);
    }

    #[Test]
    public function getConfigurationIgnoresInternalTypo3Links(): void
    {
        $configuration = $this->subject->getConfiguration(useCache: false);

        self::assertNotEmpty($configuration);
        self::assertArrayHasKey(1, $configuration);
        self::assertCount(1, $configuration[1], 'Only the external href should be extracted from content uid 1');
        self::assertSame('https://external.example/test', $configuration[1][0]['href']);
    }

    #[Test]
    public function getGroupeConfigurationGroupsLinksByHref(): void
    {
        $configuration = $this->subject->getGroupeConfiguration(useCache: false);

        self::assertArrayHasKey('https://external.example/test', $configuration);
        self::assertCount(1, $configuration['https://external.example/test']);
        self::assertSame('https://external.example/test', $configuration['https://external.example/test'][0]['href']);
        self::assertSame('Example', $configuration['https://external.example/test'][0]['title']);
    }

    #[Test]
    public function getConfigurationUsesCacheWhenFresh(): void
    {
        $cacheFile = Environment::getVarPath() . '/cache/data/links.json';
        GeneralUtility::mkdir_deep(dirname($cacheFile));
        GeneralUtility::writeFileToTypo3tempDir(
            $cacheFile,
            json_encode([99 => [['href' => 'https://cached.example']]], JSON_THROW_ON_ERROR),
        );
        touch($cacheFile, time());

        $configuration = $this->subject->getConfiguration(useCache: true);

        self::assertSame([99 => [['href' => 'https://cached.example']]], $configuration);
    }

    #[Test]
    public function getGroupeConfigurationUsesCacheWhenFresh(): void
    {
        $cacheFile = Environment::getVarPath() . '/cache/data/links_grouped.json';
        GeneralUtility::mkdir_deep(dirname($cacheFile));
        $cached = ['https://cached-grouped.example/' => [['href' => 'https://cached-grouped.example/']]];
        GeneralUtility::writeFileToTypo3tempDir($cacheFile, json_encode($cached, JSON_THROW_ON_ERROR));
        touch($cacheFile, time());

        $configuration = $this->subject->getGroupeConfiguration(useCache: true);

        self::assertSame($cached, $configuration);
    }

    #[Test]
    public function getConfigurationExtractsLinksFromMultipleContentElements(): void
    {
        $configuration = $this->subject->getConfiguration(useCache: false);

        self::assertArrayHasKey(1, $configuration);
        self::assertArrayHasKey(2, $configuration);
        self::assertSame('https://second.example/page', $configuration[2][0]['href']);
        self::assertSame('Root', $configuration[2][0]['title']);
    }

    #[Test]
    public function getConfigurationWritesCacheFileWhenCacheEnabled(): void
    {
        $cacheFile = Environment::getVarPath() . '/cache/data/links.json';
        $this->clearLinkCaches();

        $this->subject->getConfiguration(useCache: true);

        self::assertFileExists($cacheFile);
        $decoded = json_decode((string)file_get_contents($cacheFile), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        self::assertArrayHasKey(1, $decoded);
    }

    #[Test]
    public function getGroupeConfigurationIncludesTargetAttribute(): void
    {
        $configuration = $this->subject->getGroupeConfiguration(useCache: false);

        self::assertArrayHasKey('https://second.example/page', $configuration);
        self::assertSame('_blank', $configuration['https://second.example/page'][0]['target']);
    }

    private function clearLinkCaches(): void
    {
        foreach (['links.json', 'links_grouped.json'] as $cacheFileName) {
            $cacheFile = Environment::getVarPath() . '/cache/data/' . $cacheFileName;
            if (is_file($cacheFile)) {
                unlink($cacheFile);
            }
        }
    }
}
