<?php
// CI: transport.zip собран корректно и инструменты компонента находятся на любой ОС.

require __DIR__ . '/../vendor/autoload.php';

use ComponentBuilder\Process;

$project = $argv[1] ?? 'demo-project';
$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$zips = glob("{$project}/core/packages/demo-*.transport.zip");
if (!$zips) {
    $fail("transport.zip not found in {$project}/core/packages");
}

$zip = new ZipArchive();
if ($zip->open($zips[0]) !== true) {
    $fail("cannot open {$zips[0]}");
}

$hasManifest = false;
for ($i = 0; $i < $zip->numFiles; $i++) {
    $name = $zip->getNameIndex($i);
    if (str_contains($name, '\\')) {
        $fail("backslash in zip entry: {$name}");
    }
    if (preg_match('#(^|/)(tests/|phpunit\.xml)#', $name)) {
        $fail("dev file in zip: {$name}");
    }
    $hasManifest = $hasManifest || str_ends_with($name, '/manifest.php');
}
$zip->close();

if (!$hasManifest) {
    $fail('manifest.php missing in zip');
}

$component = "{$project}/core/components/demo";
$phpunit = Process::resolve('vendor/bin/phpunit', $component);
if ($phpunit === null) {
    $fail('vendor/bin/phpunit not resolved');
}
[$rc, $out] = Process::run([...$phpunit, '--version'], $component);
if ($rc !== 0) {
    $fail("phpunit --version exited {$rc}: {$out}");
}

echo "OK: " . basename($zips[0]) . ', ' . trim($out) . "\n";
