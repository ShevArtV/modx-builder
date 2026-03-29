<?php

declare(strict_types=1);

namespace Modx3TestUtils;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 */
trait MockModxTrait
{
    protected MockObject $modx;
    protected array $modxOptions = [];
    private array $getObjectMap = [];
    private array $getCollectionMap = [];

    protected function setUpModxMock(): void
    {
        $this->modx = $this->createModxMock();
        $this->configureModxServices();
        $this->configureModxLexicon();
        $this->configureModxCacheManager();
    }

    protected function mockGetObject(string $class, ?object $return): void
    {
        $this->getObjectMap[$class] = $return;
    }

    protected function mockGetCollection(string $class, array $return): void
    {
        $this->getCollectionMap[$class] = $return;
    }

    protected function createQueryMock(): MockObject
    {
        $query = $this->getMockBuilder(\xPDO\Om\xPDOQuery::class)
            ->disableOriginalConstructor()
            ->addMethods(['where', 'sortby', 'select', 'limit', 'leftJoin', 'innerJoin', 'rightJoin', 'groupby', 'having'])
            ->onlyMethods(['prepare'])
            ->getMock();

        $query->method('where')->willReturnSelf();
        $query->method('sortby')->willReturnSelf();
        $query->method('select')->willReturnSelf();
        $query->method('limit')->willReturnSelf();
        $query->method('leftJoin')->willReturnSelf();
        $query->method('innerJoin')->willReturnSelf();
        $query->method('rightJoin')->willReturnSelf();
        $query->method('groupby')->willReturnSelf();
        $query->method('having')->willReturnSelf();

        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn([]);

        $query->method('prepare')->willReturn($stmt);

        return $query;
    }

    private function createModxMock(): MockObject
    {
        $modx = $this->getMockBuilder(\MODX\Revolution\modX::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'log',
                'newQuery',
                'getSelectColumns',
                'getObject',
                'getCollection',
                'getIterator',
                'getOption',
                'addPackage',
                'getCount',
            ])
            ->getMock();

        $modx->method('log');
        $modx->method('newQuery')->willReturnCallback(fn () => $this->createQueryMock());
        $modx->method('getSelectColumns')->willReturn('');
        $modx->method('addPackage')->willReturn(true);
        $modx->method('getCount')->willReturn(0);

        $modx->method('getObject')->willReturnCallback(
            fn (string $class) => $this->getObjectMap[$class] ?? null
        );

        $modx->method('getCollection')->willReturnCallback(
            fn (string $class) => $this->getCollectionMap[$class] ?? []
        );

        $modx->method('getIterator')->willReturnCallback(
            fn (string $class) => new \ArrayIterator($this->getCollectionMap[$class] ?? [])
        );

        $modx->method('getOption')->willReturnCallback(
            fn (string $key, $options = null, $default = null) => $this->modxOptions[$key] ?? $default
        );

        return $modx;
    }

    private function configureModxServices(): void
    {
        $container = $this->createMock(\Psr\Container\ContainerInterface::class);
        $container->method('has')->willReturn(false);
        $container->method('get')->willReturn(null);

        $this->modx->services = $container;
    }

    private function configureModxLexicon(): void
    {
        $lexicon = $this->getMockBuilder(\MODX\Revolution\modLexicon::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load'])
            ->addMethods(['__invoke'])
            ->getMock();

        $lexicon->method('load');
        $lexicon->method('__invoke')->willReturnCallback(
            fn (string $key) => $key
        );

        $this->modx->lexicon = $lexicon;
    }

    private function configureModxCacheManager(): void
    {
        $cache = $this->getMockBuilder(\MODX\Revolution\modCacheManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'set', 'delete', 'refresh'])
            ->getMock();

        $cache->method('get')->willReturn(null);
        $cache->method('set')->willReturn(true);
        $cache->method('delete')->willReturn(true);
        $cache->method('refresh')->willReturn(true);

        $this->modx->cacheManager = $cache;
    }
}
