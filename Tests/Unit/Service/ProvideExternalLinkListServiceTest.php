<?php

declare(strict_types=1);

namespace Cru\ExternalLinkList\Tests\Unit\Service;

use Cru\ExternalLinkList\Service\ProvideExternalLinkListService;
use Doctrine\DBAL\Result;
use RuntimeException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ProvideExternalLinkListServiceTest extends UnitTestCase
{
    private ProvideExternalLinkListService $subject;

    /**
     * @test
     */
    public function getConfigurationReturnsPagesWithPlainExternalUrl(): void
    {
        $this->mockPagesQuery([
            [
                'uid' => 1,
                'title' => 'External',
                'url' => 'https://example.com',
                'doktype' => 3,
            ],
            [
                'uid' => 2,
                'title' => 'Internal resource',
                'url' => 't3://page?uid=10',
                'doktype' => 3,
            ],
        ]);

        $result = $this->subject->getConfiguration();

        self::assertCount(1, $result);
        self::assertSame(1, $result[0]['uid']);
        self::assertSame('https://example.com', $result[0]['url']);
    }

    /**
     * @test
     */
    public function getConfigurationIncludesPageWithUrlTypolinkInLinkField(): void
    {
        $linkService = $this->createMock(LinkService::class);
        $linkService->method('resolve')->willReturn([
            'type' => LinkService::TYPE_URL,
            'url' => 'https://other.example/',
        ]);

        $this->mockPagesQuery([
            [
                'uid' => 3,
                'title' => 'Typolink URL',
                'url' => '',
                'link' => 'https://other.example/',
                'doktype' => 3,
            ],
        ], $linkService);

        $result = $this->subject->getConfiguration();

        self::assertCount(1, $result);
        self::assertSame('https://other.example/', $result[0]['url']);
    }

    /**
     * @test
     */
    public function getConfigurationExcludesInternalTypolinkPage(): void
    {
        $linkService = $this->createMock(LinkService::class);
        $linkService->method('resolve')->willReturn([
            'type' => 'page',
            'pageuid' => 5,
        ]);

        $this->mockPagesQuery([
            [
                'uid' => 4,
                'title' => 'Internal page link',
                'url' => '',
                'link' => 't3://page?uid=5',
                'doktype' => 3,
            ],
        ], $linkService);

        $result = $this->subject->getConfiguration();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getConfigurationExcludesPageWithEmptyUrlAndLink(): void
    {
        $this->mockPagesQuery([
            [
                'uid' => 6,
                'title' => 'Empty link page',
                'url' => '',
                'link' => '',
                'doktype' => 3,
            ],
            [
                'uid' => 7,
                'title' => 'Valid',
                'url' => 'https://valid.example/',
                'doktype' => 3,
            ],
        ]);

        $result = $this->subject->getConfiguration();

        self::assertCount(1, $result);
        self::assertSame(7, $result[0]['uid']);
    }

    /**
     * @test
     */
    public function getConfigurationFallsBackToValidUrlWhenLinkServiceThrows(): void
    {
        $linkService = $this->createMock(LinkService::class);
        $linkService->method('resolve')->willThrowException(new RuntimeException('resolve failed'));

        $this->mockPagesQuery([
            [
                'uid' => 5,
                'title' => 'Fallback URL',
                'url' => '',
                'link' => 'https://fallback.example/',
                'doktype' => 3,
            ],
        ], $linkService);

        $result = $this->subject->getConfiguration();

        self::assertCount(1, $result);
        self::assertSame('https://fallback.example/', $result[0]['url']);
    }

    /**
     * @param list<array<string, mixed>> $pages
     */
    private function mockPagesQuery(array $pages, ?LinkService $linkService = null): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn($pages);

        $expressionBuilder = $this->createMock(ExpressionBuilder::class);
        $expressionBuilder->method('eq')->willReturn('1=1');

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('expr')->willReturn($expressionBuilder);
        $queryBuilder->method('executeQuery')->willReturn($result);

        $connectionPool = $this->createMock(ConnectionPool::class);
        $connectionPool->method('getQueryBuilderForTable')->with('pages')->willReturn($queryBuilder);

        $this->subject = new ProvideExternalLinkListService(
            $connectionPool,
            $linkService ?? $this->createMock(LinkService::class),
        );
    }
}
