<?php

declare(strict_types=1);

namespace Cru\ExternalLinkList\Tests\Unit\Service;

use Cru\ExternalLinkList\Service\ProvideParsedLinkListService;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ProvideParsedLinkListServiceTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getConfigurationReturnsCachedDataWhenCacheIsFresh(): void
    {
        $cacheFile = Environment::getVarPath() . '/cache/data/links.json';
        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $cached = [99 => [0 => ['href' => 'https://cached.example/', 'uid' => 99, 'pid' => 1, 'title' => 'Cached']]];
        file_put_contents($cacheFile, json_encode($cached, JSON_THROW_ON_ERROR));
        touch($cacheFile, time());
        $this->testFilesToDelete[] = $cacheFile;

        $subject = new ProvideParsedLinkListService();

        $result = $subject->getConfiguration(true);

        self::assertSame($cached, $result);
    }

    /**
     * @test
     */
    public function getGroupeConfigurationReturnsCachedDataWhenCacheIsFresh(): void
    {
        $cacheFile = Environment::getVarPath() . '/cache/data/links_grouped.json';
        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $cached = [
            'https://cached-grouped.example/' => [
                ['href' => 'https://cached-grouped.example/', 'uid' => 1, 'pid' => 1, 'title' => 'Cached', 'target' => ''],
            ],
        ];
        file_put_contents($cacheFile, json_encode($cached, JSON_THROW_ON_ERROR));
        touch($cacheFile, time());
        $this->testFilesToDelete[] = $cacheFile;

        $subject = new ProvideParsedLinkListService();

        $result = $subject->getGroupeConfiguration(true);

        self::assertSame($cached, $result);
    }
}
