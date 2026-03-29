<?php

declare(strict_types=1);

namespace Modx3TestUtils;

use PHPUnit\Framework\TestCase;

abstract class ModxTestCase extends TestCase
{
    use MockModxTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpModxMock();
    }
}
