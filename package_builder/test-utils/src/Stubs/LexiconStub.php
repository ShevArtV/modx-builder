<?php

declare(strict_types=1);

namespace Modx3TestUtils\Stubs;

use MODX\Revolution\modLexicon;

class LexiconStub extends modLexicon
{
    public function __construct()
    {
    }

    public function __invoke(string $key): string
    {
        return $key;
    }
}
