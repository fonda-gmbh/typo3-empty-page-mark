<?php

declare(strict_types=1);

namespace Fonda\EmptyPageMark\Tests\Unit\EventListener;

use Doctrine\DBAL\Result;
use Fonda\EmptyPageMark\EventListener\ModifyPageTreeItems;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Backend\Controller\Event\AfterPageTreeItemsPreparedEvent;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Localization\LanguageService;

final class ModifyPageTreeItemsTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['LANG'] = $this->createStub(LanguageService::class);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['LANG']);
    }

    #[Test]
    public function marksOnlyEmptyPagesWhoseDoktypeIsNotConfiguredAsIgnored(): void
    {
        $extensionConfiguration = $this->createStub(ExtensionConfiguration::class);
        $extensionConfiguration->method('get')->willReturn('1, 254');
        $event = new AfterPageTreeItemsPreparedEvent(null, null, [
            ['_page' => ['uid' => 1, 'doktype' => 1]],
            ['_page' => ['uid' => 2, 'doktype' => 199]],
        ]);

        (new ModifyPageTreeItems($this->emptyContentConnectionPool(), $extensionConfiguration))($event);

        $items = $event->getItems();
        self::assertArrayNotHasKey('statusInformation', $items[0]);
        self::assertCount(1, $items[1]['statusInformation']);
    }

    private function emptyContentConnectionPool(): ConnectionPool
    {
        $result = $this->createStub(Result::class);
        $result->method('fetchOne')->willReturn(0);
        $queryBuilder = $this->createStub(QueryBuilder::class);
        $queryBuilder->method('count')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('expr')->willReturn($this->createStub(ExpressionBuilder::class));
        $queryBuilder->method('executeQuery')->willReturn($result);
        $connectionPool = $this->createStub(ConnectionPool::class);
        $connectionPool->method('getQueryBuilderForTable')->willReturn($queryBuilder);
        return $connectionPool;
    }
}
