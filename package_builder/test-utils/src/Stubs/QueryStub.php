<?php

declare(strict_types=1);

namespace Modx3TestUtils\Stubs;

use xPDO\Om\xPDOQuery;

class QueryStub extends xPDOQuery
{
    public function __construct()
    {
    }

    public function where($conditions, $conjunction = xPDOQuery::SQL_AND, $binding = null, $condGroup = 0): static
    {
        return $this;
    }

    public function sortby($column, $direction = 'ASC'): static
    {
        return $this;
    }

    public function select($columns = '*'): static
    {
        return $this;
    }

    public function limit($limit, $offset = 0): static
    {
        return $this;
    }

    public function leftJoin($class, $alias = '', $conditions = []): static
    {
        return $this;
    }

    public function innerJoin($class, $alias = '', $conditions = []): static
    {
        return $this;
    }

    public function rightJoin($class, $alias = '', $conditions = []): static
    {
        return $this;
    }

    public function groupby($column, $direction = ''): static
    {
        return $this;
    }

    public function having($conditions): static
    {
        return $this;
    }
}
