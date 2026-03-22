<?php

namespace ComponentBuilder;

class ToolsChecker
{
    private string $corePath;
    private array $config;
    private array $errors = [];

    public function __construct(string $corePath, array $packageConfig)
    {
        $this->corePath = rtrim($corePath, '/');
        $this->config = $packageConfig;
    }

    public function runChecks(): bool
    {
        $this->errors = [];
        $passed = true;

        if ($this->hasComposerDependency('phpstan/phpstan')) {
            if (!$this->runPhpStan()) {
                $passed = false;
            }
        }

        $tools = $this->config['tools'] ?? [];

        if (!empty($tools['phpCsFixer']) && $this->hasComposerDependency('friendsofphp/php-cs-fixer')) {
            $mode = $tools['csFixerMode'] ?? 'fix';
            if (!$this->runCsFixer($mode)) {
                $passed = false;
            }
        }

        if (!empty($tools['eslint']) && file_exists($this->corePath . '/eslint.config.js')) {
            if (!$this->runEslint()) {
                $passed = false;
            }
        }

        return $passed;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function runPhpStan(): bool
    {
        $configFile = $this->corePath . '/phpstan.neon';

        if (!file_exists($configFile)) {
            return true;
        }

        echo "\nRunning PHPStan...\n";

        $bin = $this->corePath . '/vendor/bin/phpstan';

        if (!file_exists($bin)) {
            echo "  PHPStan not installed, skipping (run 'composer install' in {$this->corePath})\n";
            return true;
        }

        $cmd = escapeshellarg($bin) . ' analyse --no-progress --configuration=' . escapeshellarg($configFile);
        $output = [];
        $exitCode = 0;

        exec("cd " . escapeshellarg($this->corePath) . " && {$cmd} 2>&1", $output, $exitCode);

        $outputStr = implode("\n", $output);
        echo $outputStr . "\n";

        if ($exitCode !== 0) {
            $this->errors[] = 'PHPStan found errors';
            return false;
        }

        echo "  PHPStan: OK\n";
        return true;
    }

    private function runCsFixer(string $mode): bool
    {
        $configFile = $this->corePath . '/.php-cs-fixer.dist.php';

        if (!file_exists($configFile)) {
            return true;
        }

        $bin = $this->corePath . '/vendor/bin/php-cs-fixer';

        if (!file_exists($bin)) {
            echo "  PHP CS Fixer not installed, skipping (run 'composer install' in {$this->corePath})\n";
            return true;
        }

        if ($mode === 'fix') {
            echo "\nRunning PHP CS Fixer (auto-fix)...\n";
            $cmd = escapeshellarg($bin) . ' fix --config=' . escapeshellarg($configFile);
        } else {
            echo "\nRunning PHP CS Fixer (check)...\n";
            $cmd = escapeshellarg($bin) . ' fix --dry-run --diff --config=' . escapeshellarg($configFile);
        }

        $output = [];
        $exitCode = 0;

        exec("cd " . escapeshellarg($this->corePath) . " && {$cmd} 2>&1", $output, $exitCode);

        $outputStr = implode("\n", $output);
        echo $outputStr . "\n";

        if ($exitCode !== 0 && $mode !== 'fix') {
            $this->errors[] = 'PHP CS Fixer found code style issues';
            return false;
        }

        echo "  PHP CS Fixer: OK\n";
        return true;
    }

    private function runEslint(): bool
    {
        echo "\nRunning ESLint...\n";

        $nodeModules = $this->corePath . '/node_modules/.bin/eslint';

        if (!file_exists($nodeModules)) {
            echo "  ESLint not installed, skipping (run 'npm install' in {$this->corePath})\n";
            return true;
        }

        $cmd = escapeshellarg($nodeModules) . ' assets/';
        $output = [];
        $exitCode = 0;

        exec("cd " . escapeshellarg($this->corePath) . " && {$cmd} 2>&1", $output, $exitCode);

        $outputStr = implode("\n", $output);

        if (!empty($outputStr)) {
            echo $outputStr . "\n";
        }

        if ($exitCode !== 0) {
            $this->errors[] = 'ESLint found errors';
            return false;
        }

        echo "  ESLint: OK\n";
        return true;
    }

    private function hasComposerDependency(string $package): bool
    {
        $composerFile = $this->corePath . '/composer.json';

        if (!file_exists($composerFile)) {
            return false;
        }

        $composer = json_decode(file_get_contents($composerFile), true);
        $requireDev = $composer['require-dev'] ?? [];

        return isset($requireDev[$package]);
    }
}
