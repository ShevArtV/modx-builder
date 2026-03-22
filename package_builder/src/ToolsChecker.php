<?php

namespace ComponentBuilder;

class ToolsChecker
{
    private string $corePath;
    private array $config;
    private array $errors = [];

    private const DEFAULTS = [
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
            'analyse' => 'Static analysis',
            'cs' => 'Code style',
            'lint' => 'Linting',
        ];

        $label = $labels[$name] ?? $name;
        echo "\nRunning {$label}...\n";

        $bin = explode(' ', $command)[0];
        $binPath = $this->corePath . '/' . $bin;

        if (!file_exists($binPath) && !$this->isGlobalCommand($bin)) {
            echo "  {$bin} not installed, skipping\n";
            return true;
        }

        $output = [];
        $exitCode = 0;

        exec("cd " . escapeshellarg($this->corePath) . " && {$command} 2>&1", $output, $exitCode);

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

    private function isGlobalCommand(string $bin): bool
    {
        $result = trim(shell_exec("which {$bin} 2>/dev/null") ?? '');
        return !empty($result);
    }
}
