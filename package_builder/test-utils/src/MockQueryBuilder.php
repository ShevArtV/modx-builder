<?php

declare(strict_types=1);

namespace Modx3TestUtils;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MockQueryBuilder
{
    private array $rows = [];
    private array $columns = [];

    private function __construct(private readonly TestCase $test)
    {
    }

    public static function create(TestCase $test): self
    {
        return new self($test);
    }

    public function withRows(array $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    public function withColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function build(): MockObject
    {
        $query = $this->test->getMockBuilder(\xPDO\Om\xPDOQuery::class)
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

        $stmt = $this->test->createStub(\PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn($this->rows);

        if (!empty($this->columns)) {
            $stmt->method('fetch')->willReturnOnConsecutiveCalls(
                ...array_map(fn (array $row) => array_intersect_key($row, array_flip($this->columns)), $this->rows),
                false
            );
        }

        $query->method('prepare')->willReturn($stmt);

        return $query;
    }
}
