<?php

namespace ComponentBuilder;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class LexiconExtractor
{
    private array $lexiconKeys = [];

    public function extractFromDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            echo "Directory not found: {$directory}\n";
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $path = $file->getPathname();
            if (str_contains($path, '/vendor/') || str_contains($path, '\\vendor\\')) {
                continue;
            }
            $this->extractFromFile($path);
        }
    }

    private function extractFromFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);

        preg_match_all('/\$modx->lexicon\([\'"]([a-zA-Z0-9_]+)[\'"]/', $content, $matches);

        if (empty($matches[1])) {
            return;
        }

        $lastFoundLine = 0;
        foreach ($matches[1] as $index => $key) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
                continue;
            }

            $lineNumber = $this->findLineNumber($matches[0][$index], $lines, $lastFoundLine);
            if ($lineNumber > 0) {
                $lastFoundLine = $lineNumber;
            }
            $className = $this->extractClassName($content);
            $methodName = $this->extractMethodName($content, $lineNumber);

            if (!isset($this->lexiconKeys[$key])) {
                $this->lexiconKeys[$key] = [
                    'file' => basename($filePath),
                    'class' => $className,
                    'method' => $methodName,
                    'line' => $lineNumber,
                ];
            }
        }
    }

    private function findLineNumber(string $match, array $lines, int $startLine = 0): int
    {
        for ($i = $startLine; $i < count($lines); $i++) {
            if (str_contains($lines[$i], $match)) {
                return $i + 1;
            }
        }
        return 0;
    }

    private function extractClassName(string $content): string
    {
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }
        return 'unknown';
    }

    private function extractMethodName(string $content, int $lineNumber): string
    {
        $lines = explode("\n", $content);
        $currentLine = $lineNumber - 1;

        for ($i = $currentLine; $i >= 0; $i--) {
            if (preg_match('/(public|private|protected)\s+function\s+(\w+)/', $lines[$i], $matches)) {
                return $matches[2];
            }
        }

        return 'unknown';
    }

    public function generateLexiconFile(string $packageName, string $outputPath): void
    {
        $lexiconData = [];

        foreach ($this->lexiconKeys as $key => $info) {
            $lexiconData[$key] = $key;
        }

        ksort($lexiconData);

        $content = "<?php\n\n";
        foreach ($lexiconData as $key => $value) {
            $safeValue = addcslashes((string) $value, "'\\");
            $content .= "\$_lang['{$key}'] = '{$safeValue}';\n";
        }

        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        file_put_contents($outputPath, $content);
        echo "Lexicon file generated: {$outputPath}\n";
        echo "Found " . count($this->lexiconKeys) . " lexicon keys\n";
    }

    public function getKeys(): array
    {
        return $this->lexiconKeys;
    }
}
