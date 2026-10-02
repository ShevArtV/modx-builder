<?php

// Только для разработки, в transport.zip не попадает: автозагрузка компонента
// и классы MODX для PHPUnit и PHPStan.
require __DIR__ . '/vendor/autoload.php';

$modxAutoloads = [
    // компонент внутри установленного MODX: <site>/core/components/<name>
    dirname(__DIR__, 2) . '/vendor/autoload.php',
    // ядро standalone-режима, которое скачал `modxapp setup`
    (getenv('HOME') ?: getenv('USERPROFILE') ?: '') . '/.modxapp/core/vendor/autoload.php',
];

foreach ($modxAutoloads as $modxAutoload) {
    if (is_file($modxAutoload)) {
        require_once $modxAutoload;
        break;
    }
}
