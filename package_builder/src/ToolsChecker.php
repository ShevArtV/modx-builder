<?php

namespace ComponentBuilder;

class ToolsChecker
{
    private string $corePath;
    private array $config;
    private array $errors = [];

    private const DEFAULTS = [
        'test' => 'vendor/bin/phpunit --no-progress',
        'analyse' => 'vendor/bin/phpstan analyse --no-progress',
        'cs' => 'vendor/bin/php-cs-fixer fix',
        'lint' => 'node_modules/.bin/eslint assets/',
    ];

    public function __construct(string $corePath, array $packageConfig)
    {
        $this->corePath = rtrim($corePath, '/');
        $this->config = $packageConfig;
    }

    public function runChecks(): bool
    {
        $this->errors = [];
        $passed = true;
        $tools = $this->config['tools'] ?? [];

        $testCmd = $tools['test'] ?? self::DEFAULTS['test'];
        if (!empty($testCmd)) {
            if (!$this->runTool('test', $testCmd)) {
                $passed = false;
            }
        }

        $analyseCmd = $tools['analyse'] ?? self::DEFAULTS['analyse'];
        if (!empty($analyseCmd)) {
            if (!$this->runTool('analyse', $analyseCmd)) {
                $passed = false;
            }
        }

        $csCmd = $tools['cs'] ?? '';
        if (empty($csCmd) && !empty($tools['phpCsFixer'])) {
            $csCmd = self::DEFAULTS['cs'];
        }
        if (!empty($csCmd)) {
            $mode = $tools['csMode'] ?? $tools['csFixerMode'] ?? 'fix';
            if ($mode === 'check' && str_contains($csCmd, 'php-cs-fixer')) {
                $csCmd .= ' --dry-run --diff';
            }
            if (!$this->runTool('cs', $csCmd, $mode === 'fix')) {
                $passed = false;
            }
        }

        $lintCmd = $tools['lint'] ?? '';
        if (empty($lintCmd) && !empty($tools['eslint'])) {
            $lintCmd = self::DEFAULTS['lint'];
        }
        if (!empty($lintCmd)) {
            if (!$this->runTool('lint', $lintCmd)) {
                $passed = false;
            }
        }

        return $passed;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function runTool(string $name, string $command, bool $allowFailure = false): bool
    {
        $labels = [
            'test' => 'Tests',
            'analyse' => 'Static analysis',
            'cs' => 'Code style',
            'lint' => 'Linting',
        ];

        $label = $labels[$name] ?? $name;
        echo "\nRunning {$label}...\n";

        if (!$this->isAllowedCommand($command)) {
            echo "  Blocked unsafe command: {$command}\n";
            $this->errors[] = "{$label}: command blocked by security check";
            return false;
        }

        $bin = explode(' ', $command)[0];
        $binPath = $this->corePath . '/' . $bin;

        if (!file_exists($binPath) && !$this->isGlobalCommand($bin)) {
            echo "  {$bin} not installed, skipping\n";
            return true;
        }

        $output = [];
        $exitCode = 0;

        $safeCommand = $this->buildSafeCommand($command);
        exec("cd " . escapeshellarg($this->corePath) . " && {$safeCommand} 2>&1", $output, $exitCode);

        $outputStr = implode("\n", $output);
        if (!empty($outputStr)) {
            echo $outputStr . "\n";
        }

        if ($exitCode !== 0 && !$allowFailure) {
            $this->errors[] = "{$label} found errors";
            return false;
        }

        echo "  {$label}: OK\n";
        return true;
    }

    private function isAllowedCommand(string $command): bool
    {
        if (preg_match('/[;&|`$\\\]/', $command)) {
            return false;
        }

        $bin = explode(' ', $command)[0];
        $allowedBins = [
            'vendor/bin/phpunit',
            'vendor/bin/phpstan',
            'vendor/bin/php-cs-fixer',
            'vendor/bin/psalm',
            'vendor/bin/pint',
            'node_modules/.bin/eslint',
            'npx',
        ];

        return in_array($bin, $allowedBins, true);
    }

    private function buildSafeCommand(string $command): string
    {
        $parts = explode(' ', $command);
        $bin = array_shift($parts);
        $args = array_map('escapeshellarg', $parts);

        return $bin . ' ' . implode(' ', $args);
    }

    private function isGlobalCommand(string $bin): bool
    {
        $result = trim(shell_exec("which " . escapeshellarg($bin) . " 2>/dev/null") ?? '');
        return !empty($result);
    }
}
